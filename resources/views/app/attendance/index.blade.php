@extends('layouts.app')

@section('title', 'Attendance')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-3 align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="date" name="date" value="{{ request('date', now()->toDateString()) }}" class="form-control" style="max-width:180px;">
            <button class="btn btn-outline-primary"><i class="fas fa-filter"></i></button>
        </form>
        <span class="badge text-bg-success">{{ $todayCount }} check-in(s) today</span>
    </div>
    <a href="{{ route('app.attendance.create') }}" class="btn btn-primary"><i class="fas fa-calendar-plus me-1"></i> Check-in Member</a>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Member</th><th>Check-in</th><th>Check-out</th><th>Duration</th><th>Note</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>
                            <a href="{{ route('app.members.show', $record->member) }}">{{ $record->member->name }}</a>
                            <span class="text-muted small"> ({{ $record->member->member_code }})</span>
                        </td>
                        <td>{{ $record->check_in?->format('d M Y h:i A') }}</td>
                        <td>
                            @if($record->check_out)
                                {{ $record->check_out->format('d M Y h:i A') }}
                            @else
                                <span class="badge text-bg-warning">Open</span>
                            @endif
                        </td>
                        <td>{{ $record->durationMinutes() !== null ? $record->durationMinutes().' min' : '—' }}</td>
                        <td>{{ $record->note ?? '—' }}</td>
                        <td class="text-end">
                            @if(! $record->check_out)
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#checkout-{{ $record->id }}">
                                    <i class="fas fa-clock me-1"></i> Check-out
                                </button>
                            @endif
                            <a href="{{ route('app.attendance.edit', $record) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" action="{{ route('app.attendance.destroy', $record) }}" class="d-inline" onsubmit="return confirm('Remove this record?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No attendance records for this date.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $records->links() }}
</div>

@foreach($records as $record)
    @if(! $record->check_out)
        <div class="modal fade" id="checkout-{{ $record->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('app.attendance.checkout', $record) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-clock me-2 text-primary"></i>Check out {{ $record->member->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small">Checked in at {{ $record->check_in?->format('d M Y h:i A') }}</p>
                            <label class="form-label">Check-out time</label>
                            <input type="datetime-local" name="check_out" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-success">Confirm check-out</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection