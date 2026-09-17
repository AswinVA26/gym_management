@extends('layouts.app')

@section('title', 'Platform Settings')
@section('content')

<div class="row g-4">

    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="mb-3"><i class="fas fa-server me-2 text-primary"></i>Platform Info</h5>
            <table class="table mb-0">
                <tr><th class="text-muted">App name</th><td>{{ config('app.name') }}</td></tr>
                <tr><th class="text-muted">Environment</th><td><span class="badge bg-{{ config('app.env') === 'production' ? 'danger' : 'warning' }}">{{ config('app.env') }}</span></td></tr>
                <tr><th class="text-muted">Debug</th><td>{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</td></tr>
                <tr><th class="text-muted">App URL</th><td><code>{{ config('app.url') }}</code></td></tr>
                <tr><th class="text-muted">Default DB</th><td><code>{{ config('database.default') }}</code></td></tr>
                <tr><th class="text-muted">PHP version</th><td>{{ phpversion() }}</td></tr>
            </table>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="mb-3"><i class="fas fa-robot me-2 text-primary"></i>AI Integration</h5>
            <table class="table mb-0">
                <tr><th class="text-muted">Status</th>
                    <td>
                        <span class="badge bg-{{ $aiConfigured ? 'success' : 'secondary' }}">
                            {{ $aiConfigured ? 'Configured' : 'Not configured' }}
                        </span>
                    </td>
                </tr>
                <tr><th class="text-muted">Provider</th><td>{{ $aiProviderLabel }}</td></tr>
                <tr><th class="text-muted">Endpoint</th><td><code>{{ config('ai.base_url') ?: '—' }}</code></td></tr>
                <tr><th class="text-muted">Model</th><td><code>{{ config('ai.model') ?: '—' }}</code></td></tr>
                <tr>
                    <th class="text-muted">API key</th>
                    <td>
                        @if(config('ai.api_key'))
                            <code>{{ substr(config('ai.api_key'), 0, 6).'••••••' }}</code>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="mb-3"><i class="fas fa-user-shield me-2 text-primary"></i>Admin Account</h5>
            <form method="POST" action="{{ route('platform.settings.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">New password <span class="text-muted">(min 10, leave blank to keep current)</span></label>
                    <input type="password" id="password" name="password" class="form-control" minlength="10">
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm new password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="10">
                </div>
                <button class="btn btn-primary">Save changes</button>
            </form>
        </div>
    </div>

</div>

@endsection
