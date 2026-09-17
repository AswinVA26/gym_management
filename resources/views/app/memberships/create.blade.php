@extends('layouts.app')

@section('title', 'Assign Membership')
@section('content')

<div class="card p-4" style="max-width:640px;">
    <form method="POST" action="{{ route('app.memberships.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Member *</label>
                <select name="member_id" class="form-select" required>
                    <option value="">Select member</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>{{ $member->name }} ({{ $member->member_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Plan *</label>
                <select name="plan_id" class="form-select" required>
                    <option value="">Select plan</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" data-duration="{{ $plan->duration_months }}" @selected(old('plan_id') == $plan->id)>
                            {{ $plan->plan_name }} - ₹{{ number_format($plan->price, 2) }} / {{ $plan->duration_months }} mo
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Start date *</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Price (₹) - defaults to plan price</label>
                <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['active','expired','cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', 'active') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <div class="alert alert-light small mb-0">End date is calculated automatically from the plan duration.</div>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Assign</button>
            <a href="{{ route('app.memberships.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection