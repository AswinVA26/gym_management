@extends('layouts.app')

@section('title', 'Memberships')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select" style="max-width:180px;" onchange="this.form.submit()">
            <option value="all">All statuses</option>
            @foreach(['active','expired','cancelled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('app.memberships.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Assign Membership</a>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Member</th><th>Plan</th><th>Start</th><th>End</th><th>Price</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($memberships as $membership)
                    <tr>
                        <td>{{ $membership->member->name }}</td>
                        <td>{{ $membership->plan?->plan_name }}</td>
                        <td>{{ $membership->start_date->format('d M Y') }}</td>
                        <td>{{ $membership->end_date->format('d M Y') }}</td>
                        <td>₹{{ number_format($membership->price, 2) }}</td>
                        <td>
                            @php
                                $mBadge = ['active' => 'bg-success', 'expired' => 'bg-secondary', 'cancelled' => 'bg-danger'];
                            @endphp
                            <span class="badge {{ $mBadge[$membership->status] }}">{{ ucfirst($membership->status) }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('app.memberships.show', $membership) }}" class="btn btn-sm btn-outline-primary">View</a>
                            @if($membership->status === 'active')
                                <form method="POST" action="{{ route('app.memberships.cancel', $membership) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this membership?');">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No memberships found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $memberships->links() }}
</div>

@endsection