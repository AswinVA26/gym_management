@extends('layouts.app')

@section('title', 'Trainer - '.$trainer->name)
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-user-tie me-2 text-primary"></i>{{ $trainer->name }}</h5>
    <a href="{{ route('app.trainers.edit', $trainer) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3">
            <table class="table mb-0">
                <tr><th class="text-muted">Phone</th><td>{{ $trainer->phone }}</td></tr>
                <tr><th class="text-muted">Email</th><td>{{ $trainer->email ?? '—' }}</td></tr>
                <tr><th class="text-muted">Specialization</th><td>{{ $trainer->specialization ?? '—' }}</td></tr>
                <tr><th class="text-muted">Salary</th><td>{{ $trainer->salary !== null ? '₹'.number_format($trainer->salary, 2) : '—' }}</td></tr>
                <tr><th class="text-muted">Status</th>
                    <td><span class="badge bg-{{ $trainer->status ? 'success' : 'secondary' }}">{{ $trainer->status ? 'Active' : 'Inactive' }}</span></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-3">
            <h6 class="mb-3">Members assigned ({{ $trainer->members->count() }})</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Code</th><th>Name</th><th>Phone</th><th></th></tr></thead>
                    <tbody>
                        @forelse($trainer->members as $member)
                            <tr>
                                <td class="text-muted small">{{ $member->member_code }}</td>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->phone }}</td>
                                <td class="text-end"><a href="{{ route('app.members.show', $member) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No members assigned.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection