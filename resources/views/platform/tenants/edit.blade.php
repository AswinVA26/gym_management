@extends('layouts.app')

@section('title', 'Edit Gym - '.$tenant->name)
@section('content')

<div class="card p-4" style="max-width:640px;">
    <form method="POST" action="{{ route('platform.tenants.update', $tenant) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Gym name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $tenant->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input type="text" class="form-control" value="{{ $tenant->slug }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Custom domain</label>
                <input type="text" name="domain" class="form-control" value="{{ old('domain', $tenant->domain) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" @selected(old('status', $tenant->status) === 'active')>Active</option>
                    <option value="suspended" @selected(old('status', $tenant->status) === 'suspended')>Suspended</option>
                </select>
            </div>
            <div class="col-12">
                <div class="alert alert-light small mb-0">
                    <i class="fas fa-database me-1"></i>
                    Physical database: <code>{{ in_array(config('database.default'), ['sqlite']) ? basename($tenant->db_name) : $tenant->db_name }}</code>
                </div>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
            <a href="{{ route('platform.tenants.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection