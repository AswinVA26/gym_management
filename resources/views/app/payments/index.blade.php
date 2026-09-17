@extends('layouts.app')

@section('title', 'Payments')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-3 align-items-center">
        <span class="text-muted">Total: <strong>₹{{ number_format($total, 2) }}</strong></span>
        <span class="text-muted">This month: <strong class="text-success">₹{{ number_format($thisMonth, 2) }}</strong></span>
    </div>
    <a href="{{ route('app.payments.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Record Payment</a>
</div>

<div class="card p-3">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <label class="visually-hidden">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
        </div>
        <div class="col-auto">
            <label class="visually-hidden">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary">Filter</button>
        </div>
        <div class="col-auto">
            <a href="{{ route('app.payments.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Date</th><th>Member</th><th>Plan</th><th>Method</th><th>Amount</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at->format('d M Y') }}</td>
                        <td>{{ $payment->member->name }}</td>
                        <td>{{ $payment->membership?->plan?->plan_name ?? '—' }}</td>
                        <td><span class="badge text-bg-secondary">{{ ucfirst($payment->method) }}</span></td>
                        <td class="text-success fw-semibold">₹{{ number_format($payment->amount, 2) }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('app.payments.destroy', $payment) }}" class="d-inline" onsubmit="return confirm('Remove this payment?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</div>

@endsection