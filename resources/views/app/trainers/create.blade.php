@extends('layouts.app')

@section('title', 'Add Trainer')
@section('content')

<div class="card p-4" style="max-width:640px;">
    <form method="POST" action="{{ route('app.trainers.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full name *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone *</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Specialization</label>
                <input type="text" name="specialization" class="form-control" value="{{ old('specialization') }}" placeholder="e.g. Strength & Conditioning">
            </div>
            <div class="col-md-6">
                <label class="form-label">Monthly salary</label>
                <input type="number" step="0.01" name="salary" class="form-control" value="{{ old('salary') }}">
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', true))>
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Trainer</button>
            <a href="{{ route('app.trainers.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection