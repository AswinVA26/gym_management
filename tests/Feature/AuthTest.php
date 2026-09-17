<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_registered_account_becomes_super_admin(): void
    {
        $this->post(route('register.attempt'), [
            'name' => 'Platform Owner',
            'email' => 'owner@example.com',
            'password' => 'secretpass123',
            'password_confirmation' => 'secretpass123',
        ])->assertRedirect('/platform');

        $this->assertAuthenticated();

        $user = User::where('email', 'owner@example.com')->firstOrFail();

        $this->assertTrue($user->isSuperAdmin());
        $this->assertSame(1, User::where('role', User::ROLE_SUPER_ADMIN)->count());
    }

    public function test_registration_closes_once_a_super_admin_exists(): void
    {
        $this->makeSuperAdmin();

        $this->get(route('register'))->assertForbidden();

        $this->post(route('register.attempt'), [
            'name' => 'Intruder',
            'email' => 'hacker@example.com',
            'password' => 'secretpass123',
            'password_confirmation' => 'secretpass123',
        ])->assertForbidden();

        $this->assertSame(1, User::count());
    }

    public function test_staff_can_login_and_reach_their_gym_dashboard(): void
    {
        $tenant = $this->provisionGym();
        $this->gymAdminFor($tenant)->forceFill(['email' => 'head@example.com'])->save();

        $this->post(route('login.attempt'), [
            'email' => 'head@example.com',
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();

        // Tenant middleware resolves the user's gym and switches the DB.
        $this->get(route('app.dashboard'))->assertOk();
    }

    public function test_suspended_staff_cannot_login(): void
    {
        $tenant = $this->provisionGym();
        $admin = $this->gymAdminFor($tenant)->forceFill(['email' => 'head@example.com', 'status' => false]);
        $admin->save();

        $this->post(route('login.attempt'), [
            'email' => 'head@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_member_role_cannot_enter_gym_workspace(): void
    {
        $tenant = $this->provisionGym();
        $member = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_MEMBER]);

        $this->actingAs($member)
            ->get(route('app.dashboard'))
            ->assertForbidden();
    }
}
