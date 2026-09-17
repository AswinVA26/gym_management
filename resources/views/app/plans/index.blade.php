@extends('layouts.app')

@section('title', 'Membership Plans')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">{{ count($plans) }} plan(s)</p>
    <a href="{{ route('app.plans.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Plan</a>
</div>

<div class="row g-3">
    @forelse($plans as $plan)
        <div class="col-md-6 col-lg-4">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">{{ $plan->plan_name }}</h5>
                        <span class="text-muted small">{{ $plan->duration_months }} month(s)</span>
                    </div>
                    <span class="badge bg-{{ $plan->status ? 'success' : 'secondary' }}">{{ $plan->status ? 'Active' : 'Inactive' }}</span>
                </div>
                <h3 class="text-primary my-3">₹{{ number_format($plan->price, 2) }}</h3>
                @if($plan->description)
                    <p class="text-muted small">{{ $plan->description }}</p>
                @endif
                <div class="text-muted small mb-3">{{ $plan->memberships_count }} membership(s)</div>
                <div class="mt-auto d-flex gap-2">
                    <a href="{{ route('app.plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <a href="{{ route('app.memberships.create') }}" class="btn btn-sm btn-outline-primary">Sell</a>
                    <form method="POST" action="{{ route('app.plans.destroy', $plan) }}" class="ms-auto" onsubmit="return confirm('Delete this plan?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card p-4 text-center text-muted">No plans yet. Create your first membership plan.</div>
        </div>
    @endforelse
</div>

@endsection