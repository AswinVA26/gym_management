@extends('layouts.app')

@section('title', 'Dashboard')
@section('content')

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Total Members</div>
                    <h3 class="mb-0">{{ number_format($stats['members_count']) }}</h3>
                </div>
                <i class="fas fa-users stat-icon text-primary"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Active Memberships</div>
                    <h3 class="mb-0">{{ number_format($stats['active_memberships']) }}</h3>
                </div>
                <i class="fas fa-id-card stat-icon text-success"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Expiring in 30 days</div>
                    <h3 class="mb-0 {{ $stats['expiring_soon'] ? 'text-danger' : '' }}">{{ number_format($stats['expiring_soon']) }}</h3>
                </div>
                <i class="fas fa-hourglass-half stat-icon text-warning"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Revenue (all time)</div>
                    <h3 class="mb-0">₹{{ number_format($stats['revenue_total'], 2) }}</h3>
                </div>
                <i class="fas fa-money-bill-wave stat-icon text-success"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0"><i class="fas fa-robot me-2 text-primary"></i>AI Insights</h5>
                <span class="badge text-bg-secondary" id="ai-insights-badge">loading</span>
            </div>
            <div data-react-widget="AiInsights" data-endpoint="{{ route('app.ai.insights') }}"></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-3 h-100">
            <h5 class="mb-3"><i class="fas fa-users me-2 text-primary"></i>Recent Members</h5>
            @forelse($recentMembers as $member)
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <div>
                        <strong>{{ $member->name }}</strong>
                        <div class="text-muted small">{{ $member->member_code }} · {{ $member->trainer?->name ?? 'No trainer' }}</div>
                    </div>
                    <a href="{{ route('app.members.show', $member) }}" class="btn btn-sm btn-outline-primary">View</a>
                </div>
            @empty
                <p class="text-muted mb-0">No members yet. <a href="{{ route('app.members.create') }}">Add your first member</a>.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card p-3 h-100">
            <h5 class="mb-3"><i class="fas fa-hourglass-half me-2 text-warning"></i>Expiring Memberships (30 days)</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr><th>Member</th><th>Plan</th><th>Expires</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($expiring as $membership)
                            <tr>
                                <td>{{ $membership->member->name }}</td>
                                <td>{{ $membership->plan->plan_name }}</td>
                                <td>{{ $membership->end_date->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('app.memberships.edit', $membership) }}" class="btn btn-sm btn-outline-success">Renew</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center py-3">No memberships expiring soon.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-3 h-100">
            <h5 class="mb-3"><i class="fas fa-receipt me-2 text-success"></i>Recent Payments</h5>
            @forelse($recentPayments as $payment)
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <div>
                        <strong>{{ $payment->member->name }}</strong>
                        <div class="text-muted small">{{ $payment->method }} · {{ $payment->paid_at->format('d M Y') }}</div>
                    </div>
                    <span class="text-success fw-semibold">₹{{ number_format($payment->amount, 2) }}</span>
                </div>
            @empty
                <p class="text-muted mb-0">No payments recorded. <a href="{{ route('app.payments.create') }}">Record one</a>.</p>
            @endforelse
        </div>
    </div>
</div>

@endsection