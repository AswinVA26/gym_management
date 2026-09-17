@extends('layouts.app')

@section('title', 'New Membership Plan')
@section('content')

<div class="card p-4" style="max-width:640px;">
    <form method="POST" action="{{ route('app.plans.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Plan name *</label>
                <input type="text" name="plan_name" class="form-control @error('plan_name') is-invalid @enderror" value="{{ old('plan_name') }}" placeholder="e.g. Monthly / Quarterly" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Duration (months) *</label>
                <input type="number" min="1" max="120" name="duration_months" class="form-control" value="{{ old('duration_months', 1) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Price (₹) *</label>
                <input type="number" step="0.01" min="0" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', true))>
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Create Plan</button>
            <a href="{{ route('app.plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection