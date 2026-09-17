@extends('layouts.app')

@section('title', 'Edit Membership')
@section('content')

<div class="card p-4" style="max-width:640px;">
    <form method="POST" action="{{ route('app.memberships.update', $membership) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Member *</label>
                <select name="member_id" class="form-select" required>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" @selected(old('member_id', $membership->member_id) == $member->id)>{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Plan *</label>
                <select name="plan_id" class="form-select" required>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(old('plan_id', $membership->plan_id) == $plan->id)>{{ $plan->plan_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Start date *</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $membership->start_date->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">End date *</label>
                <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $membership->end_date->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Price (₹)</label>
                <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $membership->price) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['active','expired','cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $membership->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
            <a href="{{ route('app.memberships.show', $membership) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection