@extends('layouts.app')

@section('title', 'Member - '.$member->name)
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-user me-2 text-primary"></i>{{ $member->name }}</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('app.members.edit', $member) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
        <a href="{{ route('app.ai.index') }}?member={{ $member->id }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-robot me-1"></i> Generate workout</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3">
            <table class="table mb-0">
                <tr><th class="text-muted">Code</th><td>{{ $member->member_code }}</td></tr>
                <tr><th class="text-muted">Phone</th><td>{{ $member->phone }}</td></tr>
                <tr><th class="text-muted">Email</th><td>{{ $member->email ?? '—' }}</td></tr>
                <tr><th class="text-muted">Gender</th><td>{{ ucfirst($member->gender ?? '—') }}</td></tr>
                <tr><th class="text-muted">DOB</th><td>{{ $member->dob?->format('d M Y') ?? '—' }}</td></tr>
                <tr><th class="text-muted">Trainer</th><td>{{ $member->trainer?->name ?? '—' }}</td></tr>
                <tr><th class="text-muted">Status</th>
                    <td><span class="badge bg-{{ $member->status === 'active' ? 'success' : ($member->status === 'suspended' ? 'danger' : 'secondary') }}">{{ ucfirst($member->status) }}</span></td>
                </tr>
                <tr><th class="text-muted">Joined</th><td>{{ $member->created_at?->format('d M Y') }}</td></tr>
            </table>
            @if($member->notes)
                <div class="alert alert-light small mb-0 mt-2">{{ $member->notes }}</div>
            @endif
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card p-3 mb-3">
            <h6 class="mb-3"><i class="fas fa-id-card me-2 text-success"></i>Memberships</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Plan</th><th>Start</th><th>End</th><th>Price</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($member->memberships as $membership)
                            <tr>
                                <td>{{ $membership->plan?->plan_name }}</td>
                                <td>{{ $membership->start_date->format('d M Y') }}</td>
                                <td>{{ $membership->end_date->format('d M Y') }}</td>
                                <td>₹{{ number_format($membership->price, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $membership->status === 'active' ? 'success' : ($membership->status === 'cancelled' ? 'danger' : 'secondary') }}">{{ ucfirst($membership->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No memberships.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-3">
            <h6 class="mb-3"><i class="fas fa-receipt me-2 text-warning"></i>Payments</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Date</th><th>Method</th><th>Amount</th></tr></thead>
                    <tbody>
                        @forelse($member->payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_at->format('d M Y') }}</td>
                                <td>{{ ucfirst($payment->method) }}</td>
                                <td class="text-success fw-semibold">₹{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection