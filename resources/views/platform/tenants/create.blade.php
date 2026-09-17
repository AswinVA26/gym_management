@extends('layouts.app')

@section('title', 'Provision New Gym')
@section('content')

<div class="card p-4" style="max-width:720px;">
    <form method="POST" action="{{ route('platform.tenants.store') }}">
        @csrf
        <h6 class="mb-3"><i class="fas fa-database me-2 text-primary"></i>Gym &amp; database</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Gym name *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. IronWorks Fitness" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug * <span class="text-muted small">(database name: gym_&lt;slug&gt;)</span></label>
                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="e.g. ironworks" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Custom domain (optional)</label>
                <input type="text" name="domain" class="form-control" value="{{ old('domain') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                </select>
            </div>
        </div>

        <h6 class="mt-4 mb-3"><i class="fas fa-user-shield me-2 text-success"></i>First gym admin account <span class="text-muted small">(optional; create more later in gym Settings)</span></h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Admin name</label>
                <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Admin email</label>
                <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror" value="{{ old('admin_email') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Temporary password (min 10 chars)</label>
                <input type="password" name="admin_password" class="form-control">
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="fas fa-rocket me-1"></i> Provision Gym</button>
            <a href="{{ route('platform.tenants.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection