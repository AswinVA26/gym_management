@extends('layouts.app')

@section('title', 'Edit Member - '.$member->name)
@section('content')

<div class="card p-4" style="max-width:720px;">
    <form method="POST" action="{{ route('app.members.update', $member) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full name *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $member->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $member->email) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone *</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $member->phone) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">Select</option>
                    @foreach(['male','female','other'] as $gender)
                        <option value="{{ $gender }}" @selected(old('gender', $member->gender) === $gender)>{{ ucfirst($gender) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Date of birth</label>
                <input type="date" name="dob" class="form-control" value="{{ old('dob', $member->dob?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Assigned trainer</label>
                <select name="trainer_id" class="form-select">
                    <option value="">No trainer</option>
                    @foreach($trainers as $trainer)
                        <option value="{{ $trainer->id }}" @selected(old('trainer_id', $member->trainer_id) == $trainer->id)>{{ $trainer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['active','inactive','suspended'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $member->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $member->address) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $member->notes) }}</textarea>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Member</button>
            <a href="{{ route('app.members.show', $member) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection