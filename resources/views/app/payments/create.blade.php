@extends('layouts.app')

@section('title', 'Record Payment')
@section('content')

<div class="card p-4" style="max-width:560px;">
    <form method="POST" action="{{ route('app.payments.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Member *</label>
                <select name="member_id" class="form-select" required>
                    <option value="">Select member</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>{{ $member->name }} ({{ $member->member_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Membership (optional)</label>
                <select name="membership_id" class="form-select">
                    <option value="">No linked membership</option>
                    @foreach($memberships as $membership)
                        <option value="{{ $membership->id }}" @selected((string) old('membership_id', request('membership')) === (string) $membership->id)>
                            {{ $membership->member->name }} - {{ $membership->plan?->plan_name }} (ends {{ $membership->end_date->format('d M Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Amount (₹) *</label>
                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Method *</label>
                <select name="method" class="form-select">
                    @foreach(\App\Models\Payment::METHODS as $method)
                        <option value="{{ $method }}" @selected(old('method', 'cash') === $method)>{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Payment date *</label>
                <input type="date" name="paid_at" class="form-control" value="{{ old('paid_at', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Transaction / reference</label>
                <input type="text" name="transaction_id" class="form-control" value="{{ old('transaction_id') }}">
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Record Payment</button>
            <a href="{{ route('app.payments.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection