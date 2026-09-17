@extends('layouts.app')

@section('title', 'Gyms (Platform)')
@section('content')

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <div class="text-muted small">Total gyms</div>
            <h3 class="mb-0">{{ $gyms->total() }}</h3>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <div class="text-muted small">Active gyms</div>
            <h3 class="mb-0 text-success">{{ $gyms->where('status', 'active')->count() }}</h3>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <div class="text-muted small">Suspended</div>
            <h3 class="mb-0 text-danger">{{ $gyms->where('status', 'suspended')->count() }}</h3>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3 d-flex align-items-center justify-content-center">
            <a href="{{ route('platform.tenants.create') }}" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i> New Gym</a>
        </div>
    </div>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Gym</th><th>Slug / DB</th><th>Staff</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($gyms as $gym)
                    <tr>
                        <td><strong>{{ $gym->name }}</strong><br><span class="text-muted small">{{ $gym->domain ?? '—' }}</span></td>
                        <td>
                            <code>{{ $gym->slug }}</code><br>
                            <span class="text-muted small"><code>{{ in_array(config('database.default'), ['sqlite']) ? basename($gym->db_name) : $gym->db_name }}</code></span>
                        </td>
                        <td>{{ $gym->users_count }}</td>
                        <td><span class="badge bg-{{ $gym->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($gym->status) }}</span></td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                @if($gym->status === 'active')
                                    <form method="POST" action="{{ route('platform.tenants.switch', $gym) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i> Manage</button>
                                    </form>
                                @endif
                                <a href="{{ route('platform.tenants.edit', $gym) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form method="POST" action="{{ route('platform.tenants.toggle', $gym) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning">{{ $gym->status === 'active' ? 'Suspend' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('platform.tenants.destroy', $gym) }}"
                                    onsubmit="return confirm('PERMANENTLY delete this gym and its entire database? This cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No gyms yet. Create your first gym to provision an isolated database.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $gyms->links() }}
</div>

@endsection