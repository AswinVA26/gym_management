<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_gym_admin_can_create_member_with_generated_code(): void
    {
        $tenant = $this->provisionGym();
        $this->actingAs($this->gymAdminFor($tenant));

        $this->post(route('app.members.store'), [
            'name' => 'Arjun Verma',
            'email' => 'arjun@example.com',
            'phone' => '9812345678',
            'status' => 'active',
        ])->assertRedirect(route('app.members.index'));

        $this->assertSame(1, Member::count());

        $member = Member::firstOrFail();

        $this->assertSame('Arjun Verma', $member->name);
        $this->assertNotEmpty($member->member_code);
        $this->assertStringStartsWith('MB-', $member->member_code);
    }

    public function test_duplicate_member_email_is_rejected(): void
    {
        $tenant = $this->provisionGym();
        $this->actingAs($this->gymAdminFor($tenant));

        $payload = [
            'name' => 'Arjun Verma',
            'email' => 'arjun@example.com',
            'phone' => '9812345678',
            'status' => 'active',
        ];

        $this->post(route('app.members.store'), $payload)->assertRedirect();

        $this->post(route('app.members.store'), $payload)
            ->assertSessionHasErrors('email');

        $this->assertSame(1, Member::count());
    }

    public function test_gym_admin_can_update_a_member(): void
    {
        $tenant = $this->provisionGym();
        $this->actingAs($this->gymAdminFor($tenant));

        $this->switchToGym($tenant);
        $member = Member::create([
            'member_code' => 'MB-000001',
            'name' => 'Old Name',
            'phone' => '9812345678',
            'status' => 'active',
        ]);

        $this->put(route('app.members.update', $member), [
            'name' => 'New Name',
            'phone' => '9898989898',
            'status' => 'active',
        ])->assertRedirect(route('app.members.show', $member));

        $this->assertSame('New Name', $member->fresh()->name);
    }

    public function test_gym_admin_can_open_member_form_pages(): void
    {
        $tenant = $this->provisionGym();
        $this->actingAs($this->gymAdminFor($tenant));

        $this->get(route('app.members.create'))->assertOk();
        $this->get(route('app.members.edit', $this->makeMember($tenant)))->assertOk();

        $this->assertTrue(true);
    }

    public function test_trainer_can_view_members_but_not_manage_trainers(): void
    {
        $tenant = $this->provisionGym();
        $this->actingAs($this->trainerFor($tenant));

        $this->get(route('app.members.index'))->assertOk();

        $this->get(route('app.trainers.index'))->assertForbidden();
        $this->get(route('app.trainers.create'))->assertForbidden();
    }

    protected function makeMember(Tenant $tenant): Member
    {
        $this->switchToGym($tenant);

        return Member::create([
            'member_code' => 'MB-000001',
            'name' => 'Test Member',
            'phone' => '9812345678',
            'status' => 'active',
        ]);
    }
}
