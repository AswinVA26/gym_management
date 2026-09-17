@extends('layouts.app')

@section('title', 'Check-in Member')
@section('content')

<div class="card p-4" style="max-width:520px;">
    <form method="POST" action="{{ route('app.attendance.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Member *</label>
            <select name="member_id" class="form-select" required>
                <option value="">Select member</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>{{ $member->name }} ({{ $member->member_code }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Check-in time (defaults to now)</label>
            <input type="datetime-local" name="check_in" class="form-control" value="{{ old('check_in') }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Note</label>
            <input type="text" name="note" class="form-control" value="{{ old('note') }}">
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary"><i class="fas fa-calendar-check me-1"></i> Check in</button>
            <a href="{{ route('app.attendance.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </form>
</div>

@endsection