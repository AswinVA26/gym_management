@extends('layouts.app')

@section('title', 'Trainers')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">{{ count($trainers) }} trainer(s) on staff</p>
    <a href="{{ route('app.trainers.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Trainer</a>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Name</th><th>Specialization</th><th>Phone</th><th>Salary</th><th>Members</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($trainers as $trainer)
                    <tr>
                        <td><strong>{{ $trainer->name }}</strong></td>
                        <td>{{ $trainer->specialization ?? '—' }}</td>
                        <td>{{ $trainer->phone }}</td>
                        <td>{{ $trainer->salary !== null ? '₹'.number_format($trainer->salary, 2) : '—' }}</td>
                        <td>{{ $trainer->members_count }}</td>
                        <td>
                            <span class="badge bg-{{ $trainer->status ? 'success' : 'secondary' }}">{{ $trainer->status ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('app.trainers.show', $trainer) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="{{ route('app.trainers.edit', $trainer) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('app.trainers.destroy', $trainer) }}" class="d-inline"
                                onsubmit="return confirm('Remove this trainer?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No trainers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection