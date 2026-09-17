@extends('layouts.app')

@section('title', 'Edit Attendance')
@section('content')

<div class="card p-4" style="max-width:560px;">
    <form method="POST" action="{{ route('app.attendance.update', $attendance) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Member *</label>
            <select name="member_id" class="form-select" required>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" @selected(old('member_id', $attendance->member_id) == $member->id)>{{ $member->name }} ({{ $member->member_code }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Check-in time *</label>
            <input type="datetime-local" name="check_in" class="form-control" value="{{ old('check_in', $attendance->check_in?->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Check-out time <span class="text-muted">(leave blank to keep open)</span></label>
            <input type="datetime-local" name="check_out" class="form-control" value="{{ old('check_out', $attendance->check_out?->format('Y-m-d\TH:i')) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Note</label>
            <input type="text" name="note" class="form-control" value="{{ old('note', $attendance->note) }}" maxlength="255">
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Save changes</button>
            <a href="{{ route('app.attendance.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </form>
</div>

@endsection