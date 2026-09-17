@extends('layouts.app')

@section('title', 'Membership - '.$membership->member->name)
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ $membership->member->name }} <span class="text-muted fs-6">/ {{ $membership->plan?->plan_name }}</span></h5>
    <div class="d-flex gap-2">
        <a href="{{ route('app.memberships.edit', $membership) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
        <a href="{{ route('app.payments.create') }}?membership={{ $membership->id }}" class="btn btn-sm btn-outline-success">Record Payment</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card p-3">
            <table class="table mb-0">
                <tr><th class="text-muted">Member</th><td><a href="{{ route('app.members.show', $membership->member) }}">{{ $membership->member->name }}</a></td></tr>
                <tr><th class="text-muted">Plan</th><td>{{ $membership->plan?->plan_name }}</td></tr>
                <tr><th class="text-muted">Start</th><td>{{ $membership->start_date->format('d M Y') }}</td></tr>
                <tr><th class="text-muted">End</th>
                    <td>{{ $membership->end_date->format('d M Y') }}
                        @if($membership->status === 'active')
                            <span class="badge bg-{{ $membership->end_date->isPast() ? 'danger' : 'success' }}">{{ $membership->end_date->isPast() ? 'Overdue' : 'Active' }}</span>
                        @endif
                    </td>
                </tr>
                <tr><th class="text-muted">Price</th><td>₹{{ number_format($membership->price, 2) }}</td></tr>
                <tr><th class="text-muted">Status</th>
                    <td><span class="badge bg-{{ $membership->status === 'active' ? 'success' : ($membership->status === 'cancelled' ? 'danger' : 'secondary') }}">{{ ucfirst($membership->status) }}</span></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-3">
            <h6 class="mb-3">Payments ({{ $membership->payments->count() }})</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Date</th><th>Method</th><th>Amount</th><th></th></tr></thead>
                    <tbody>
                        @forelse($membership->payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_at->format('d M Y') }}</td>
                                <td>{{ ucfirst($payment->method) }}</td>
                                <td>₹{{ number_format($payment->amount, 2) }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('app.payments.destroy', $payment) }}" class="d-inline" onsubmit="return confirm('Remove payment?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No payments linked yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection