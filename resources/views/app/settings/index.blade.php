@extends('layouts.app')

@section('title', 'Settings')
@section('content')

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card p-4 mb-3">
            <h5 class="mb-3"><i class="fas fa-building me-2 text-primary"></i>Gym workspace</h5>
            <table class="table mb-0">
                <tr><th class="text-muted">Name</th><td>{{ $tenant->name }}</td></tr>
                <tr><th class="text-muted">Slug</th><td><code>{{ $tenant->slug }}</code></td></tr>
                <tr><th class="text-muted">Database</th><td><code>{{ in_array(config('database.default'), ['sqlite']) ? basename($tenant->db_name) : $tenant->db_name }}</code></td></tr>
                <tr><th class="text-muted">Status</th>
                    <td><span class="badge bg-{{ $tenant->isActive() ? 'success' : 'danger' }}">{{ $tenant->status }}</span></td>
                </tr>
                <tr><th class="text-muted">AI engine</th><td>{{ $aiProviderLabel }}</td></tr>
            </table>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-users-gear me-2 text-primary"></i>Staff accounts</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#newStaffForm"><i class="fas fa-plus"></i> Add staff</button>
            </div>

            <div class="collapse mb-3" id="newStaffForm">
                <form method="POST" action="{{ route('app.settings.staff.store') }}" class="bg-body-tertiary rounded p-3">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Full name" required></div>
                        <div class="col-md-4"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                        <div class="col-md-4"><input type="password" name="password" class="form-control" placeholder="Password (min 10)" required></div>
                        <div class="col-auto">
                            <select name="role" class="form-select">
                                <option value="gym_admin">Gym Admin</option>
                                <option value="trainer">Trainer</option>
                            </select>
                        </div>
                        <div class="col-auto"><button class="btn btn-primary">Create</button></div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($staff as $staffUser)
                            <tr>
                                <td>{{ $staffUser->name }}
                                    @if($staffUser->id === auth()->id()) <span class="badge text-bg-secondary">you</span> @endif
                                </td>
                                <td>{{ $staffUser->email }}</td>
                                <td>{{ \App\Models\User::roleLabel($staffUser->role) }}</td>
                                <td>
                                    <span class="badge bg-{{ $staffUser->status ? 'success' : 'danger' }}">{{ $staffUser->status ? 'Active' : 'Suspended' }}</span>
                                </td>
                                <td class="text-end">
                                    @if($staffUser->id !== auth()->id())
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#edit-staff-{{ $staffUser->id }}">Edit</button>
                                        <form method="POST" action="{{ route('app.settings.staff.destroy', $staffUser) }}" class="d-inline" onsubmit="return confirm('Remove this staff account?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @if($staffUser->id !== auth()->id())
                                <tr class="collapse" id="edit-staff-{{ $staffUser->id }}">
                                    <td colspan="5">
                                        <form method="POST" action="{{ route('app.settings.staff.update', $staffUser) }}" class="bg-body-tertiary rounded p-2 d-flex gap-2 align-items-center">
                                            @csrf @method('PUT')
                                            <input type="text" name="name" value="{{ $staffUser->name }}" class="form-control" style="max-width:180px;" required>
                                            <select name="role" class="form-select" style="max-width:140px;">
                                                <option value="gym_admin" @selected($staffUser->role === 'gym_admin')>Gym Admin</option>
                                                <option value="trainer" @selected($staffUser->role === 'trainer')>Trainer</option>
                                            </select>
                                            <input type="password" name="password" class="form-control" placeholder="New password (optional)" style="max-width:180px;">
                                            <button type="submit" name="status" value="{{ $staffUser->status ? 0 : 1 }}" class="btn btn-sm {{ $staffUser->status ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                {{ $staffUser->status ? 'Suspend' : 'Activate' }}
                                            </button>
                                            <button class="btn btn-sm btn-primary">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No staff accounts. Add one above.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection