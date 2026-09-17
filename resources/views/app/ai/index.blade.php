@extends('layouts.app')

@section('title', 'AI Assistant')
@section('content')

@php
    $memberJson = $members->map(fn ($m) => ['id' => $m->id, 'name' => $m->name, 'code' => $m->member_code])->values();
    $aiProps = json_encode([
        'members' => $memberJson,
        'preselectMember' => (int) request('member', 0),
        'providerLabel' => $aiProviderLabel,
        'configured' => $aiConfigured,
    ], JSON_THROW_ON_ERROR | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
@endphp

@if(! $aiConfigured)
    <div class="alert alert-info d-flex align-items-center gap-2">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>Built-in engine active.</strong> AI answers currently use GymHub's local rule engine.
            Connect a free endpoint in your <code>.env</code> (<kbd>AI_BASE_URL</kbd>, e.g. Ollama at <code>http://localhost:11434/v1</code>, Groq or OpenRouter) to enable full AI responses.
        </div>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0"><i class="fas fa-dumbbell me-2 text-primary"></i>Workout Plan Generator</h5>
                <span class="badge text-bg-primary">{{ $aiProviderLabel }}</span>
            </div>
            <div data-react-widget="AiWorkoutPlanGenerator" data-props="{{ $aiProps }}"></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-3">
            <h5 class="mb-2"><i class="fas fa-comments me-2 text-success"></i>Gym Copilot</h5>
            <div data-react-widget="AiAssistant" data-endpoint="{{ route('app.ai.assistant') }}"></div>
        </div>
    </div>
</div>

<div class="card p-3 mt-3">
    <h5 class="mb-3"><i class="fas fa-list me-2 text-primary"></i>Recently generated plans</h5>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>Title</th><th>Member</th><th>Level</th><th>Days</th><th>Source</th><th>Generated</th><th></th></tr></thead>
            <tbody>
                @forelse($savedPlans as $plan)
                    <tr>
                        <td>{{ $plan->title }}</td>
                        <td>{{ $plan->member?->name ?? 'General' }}</td>
                        <td>{{ ucfirst($plan->level ?? '—') }}</td>
                        <td>{{ $plan->days_per_week }} days</td>
                        <td>
                            @php($source = $plan->ai_meta['source'] ?? 'ai')
                            <span class="badge bg-{{ $source === 'builtin' ? 'secondary' : 'info' }}">
                                {{ $source === 'builtin' ? 'Built-in' : 'AI' }}
                            </span>
                        </td>
                        <td>{{ $plan->created_at?->format('d M Y H:i') }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#plan-{{ $plan->id }}">Preview</button>
                        </td>
                    </tr>
                    <tr class="collapse" id="plan-{{ $plan->id }}">
                        <td colspan="7">
                            <div class="p-2 bg-body-tertiary rounded small text-start pre-wrap"><pre class="mb-0">{{ $plan->plan }}</pre></div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">No plans generated yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<style>
    .pre-wrap pre { white-space: pre-wrap; word-break: break-word; font-family: inherit; }
</style>
@endpush