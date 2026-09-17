<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_provisioning_creates_an_isolated_gym_database(): void
    {
        $tenant = $this->provisionGym('alphafitness', 'Alpha Fitness');

        $this->switchToGym($tenant);

        $this->assertTrue(Schema::connection('tenant')->hasTable('members'));
        $this->assertTrue(Schema::connection('tenant')->hasTable('membership_plans'));
        $this->assertTrue(Schema::connection('tenant')->hasTable('payments'));

        // The platform (central) database only holds platform tables.
        $this->assertFalse(Schema::hasTable('members'));
        $this->assertTrue(Schema::hasTable('tenants'));

        $this->assertDatabaseHas('tenants', ['slug' => 'alphafitness', 'status' => 'active']);
    }

    public function test_member_records_are_isolated_between_gyms(): void
    {
        $gymA = $this->provisionGym('alphafitness', 'Alpha Fitness');
        $gymB = $this->provisionGym('betafitness', 'Beta Fitness');

        $this->switchToGym($gymA);

        Member::create([
            'member_code' => 'MB-TESTA',
            'name' => 'Alice',
            'phone' => '9811111111',
            'status' => 'active',
        ]);

        $this->assertSame(1, Member::count());

        $this->switchToGym($gymB);

        $this->assertSame(0, Member::count());
        $this->assertFalse(Member::where('member_code', 'MB-TESTA')->exists());
    }

    public function test_super_admin_can_provision_gym_with_an_admin_via_http(): void
    {
        $this->actingAs($this->makeSuperAdmin());

        $this->post(route('platform.tenants.store'), [
            'name' => 'Iron Works Gym',
            'slug' => 'ironworks',
            'status' => 'active',
            'admin_name' => 'Iron Admin',
            'admin_email' => 'iron@example.com',
            'admin_password' => 'secretpass123',
        ])->assertRedirect(route('platform.tenants.index'));

        $this->assertDatabaseHas('tenants', ['slug' => 'ironworks']);

        $tenant = Tenant::where('slug', 'ironworks')->firstOrFail();
        $admin = User::where('email', 'iron@example.com')->firstOrFail();

        $this->assertTrue($admin->isGymAdmin());
        $this->assertSame($tenant->id, $admin->tenant_id);
    }
}
