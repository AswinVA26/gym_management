<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_gym_admin_can_check_in_and_check_out_a_member(): void
    {
        $tenant = $this->provisionGym();
        $admin = $this->gymAdminFor($tenant);
        $this->actingAs($admin);
        $this->switchToGym($tenant);

        $member = Member::create([
            'member_code' => 'MB-000001',
            'name' => 'Peter Parker',
            'phone' => '9812345678',
            'status' => 'active',
        ]);

        $this->post(route('app.attendance.store'), ['member_id' => $member->id])
            ->assertRedirect(route('app.attendance.index'));

        $this->switchToGym($tenant);
        $record = Attendance::firstOrFail();
        $this->assertNull($record->check_out);

        $this->post(route('app.attendance.checkout', $record), [], [
            'HTTP_REFERER' => url('/app/attendance'),
        ])->assertRedirect();

        $this->switchToGym($tenant);
        $this->assertNotNull($record->fresh()->check_out);

        $this->get(route('app.attendance.index'))
            ->assertOk()
            ->assertSee('Check-out recorded')
            ->assertSee('min');
    }

    public function test_attendance_create_page_renders(): void
    {
        $tenant = $this->provisionGym();
        $this->actingAs($this->gymAdminFor($tenant));

        $this->get(route('app.attendance.create'))->assertOk();
    }

    public function test_trainer_can_manage_attendance(): void
    {
        $tenant = $this->provisionGym();
        $trainer = $this->trainerFor($tenant);
        $this->switchToGym($tenant);

        $member = Member::create([
            'member_code' => 'MB-000001',
            'name' => 'Peter Parker',
            'phone' => '9812345678',
            'status' => 'active',
        ]);

        $this->actingAs($trainer)
            ->post(route('app.attendance.store'), ['member_id' => $member->id])
            ->assertRedirect(route('app.attendance.index'));

        $this->switchToGym($tenant);
        $record = Attendance::firstOrFail();

        $this->actingAs($trainer)
            ->post(route('app.attendance.checkout', $record), [], [
                'HTTP_REFERER' => url('/app/attendance'),
            ])->assertRedirect();

        $this->switchToGym($tenant);
        $this->assertNotNull($record->fresh()->check_out);
    }

    public function test_prevents_duplicate_open_check_ins(): void
    {
        $tenant = $this->provisionGym();
        $this->actingAs($this->gymAdminFor($tenant));
        $this->switchToGym($tenant);

        $member = Member::create([
            'member_code' => 'MB-000001',
            'name' => 'Peter Parker',
            'phone' => '9812345678',
            'status' => 'active',
        ]);

        $this->post(route('app.attendance.store'), ['member_id' => $member->id])->assertRedirect();

        $this->post(route('app.attendance.store'), ['member_id' => $member->id])
            ->assertStatus(422);

        $this->switchToGym($tenant);
        $this->assertSame(1, Attendance::count());
    }
}
