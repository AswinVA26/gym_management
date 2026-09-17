<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Trainer;

class GymStats
{
    /**
     * Compute live analytics for the ACTIVE tenant database.
     * Call only when a tenant connection is active.
     *
     * @return array<string, mixed>
     */
    public static function collect(int $expiringDays = 30): array
    {
        return [
            'members_count' => Member::count(),
            'active_members' => Member::active()->count(),
            'inactive_members' => Member::where('status', 'inactive')->count(),
            'trainers_count' => Trainer::count(),
            'plans_count' => MembershipPlan::count(),
            'active_memberships' => Membership::active()->count(),
            'expiring_soon' => Membership::expiringIn($expiringDays)->count(),
            'revenue_total' => (float) Payment::sum('amount'),
            'revenue_this_month' => (float) Payment::where('paid_at', '>=', now()->startOfMonth())->sum('amount'),
            'attendance_today' => Attendance::whereDate('check_in', now()->toDateString())->count(),
            'attendance_this_week' => Attendance::where('check_in', '>=', now()->startOfWeek())->count(),
            'plan_counts' => MembershipPlan::query()
                ->withCount(['memberships as active_count' => fn ($q) => $q->where('status', Membership::STATUS_ACTIVE)])
                ->pluck('active_count', 'plan_name')
                ->all(),
        ];
    }
}
