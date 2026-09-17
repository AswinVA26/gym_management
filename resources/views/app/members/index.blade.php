@extends('layouts.app')

@section('title', 'Members')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="GET" action="{{ route('app.members.index') }}" class="d-flex gap-2 flex-wrap">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, code, phone..." class="form-control" style="max-width:280px;">
        <select name="status" class="form-select" style="max-width:160px;">
            <option value="all">All statuses</option>
            @foreach(['active','inactive','suspended'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
    </form>
    <a href="{{ route('app.members.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Member</a>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Code</th><th>Name</th><th>Phone</th><th>Trainer</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    <tr>
                        <td class="text-muted small">{{ $member->member_code }}</td>
                        <td><strong>{{ $member->name }}</strong></td>
                        <td>{{ $member->phone }}</td>
                        <td>{{ $member->trainer?->name ?? '—' }}</td>
                        <td>
                            @php
                                $badge = ['active' => 'bg-success', 'inactive' => 'bg-secondary', 'suspended' => 'bg-danger'];
                            @endphp
                            <span class="badge {{ $badge[$member->status] }}">{{ ucfirst($member->status) }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('app.members.show', $member) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="{{ route('app.members.edit', $member) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('app.members.destroy', $member) }}" class="d-inline"
                                onsubmit="return confirm('Delete this member and all their records?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No members found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $members->links() }}
</div>

@endsection