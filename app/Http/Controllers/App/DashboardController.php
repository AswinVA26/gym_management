<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Services\GymStats;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = GymStats::collect();

        $recentMembers = Member::with('trainer')->latest()->limit(6)->get();
        $recentPayments = Payment::with('member')->latest()->limit(6)->get();
        $expiring = Membership::with('member', 'plan')
            ->expiringIn(30)
            ->latest('end_date')
            ->limit(6)
            ->get();

        // Keep stale "active" memberships honest.
        Membership::where('status', Membership::STATUS_ACTIVE)
            ->where('end_date', '<', now()->toDateString())
            ->update(['status' => Membership::STATUS_EXPIRED]);

        return view('app.dashboard.index', compact('stats', 'recentMembers', 'recentPayments', 'expiring'));
    }
}
