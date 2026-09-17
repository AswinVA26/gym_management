<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage-payments');

        $payments = Payment::query()
            ->with('member', 'membership.plan')
            ->when($request->filled('from'), fn ($q) => $q->whereDate('paid_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('paid_at', '<=', $request->input('to')))
            ->latest('paid_at')
            ->paginate(15)
            ->withQueryString();

        $total = (float) Payment::sum('amount');
        $thisMonth = (float) Payment::where('paid_at', '>=', now()->startOfMonth())->sum('amount');

        return view('app.payments.index', compact('payments', 'total', 'thisMonth'));
    }

    public function create()
    {
        $this->authorize('manage-payments');

        $members = Member::all();
        $memberships = Membership::active()->with('member', 'plan')->latest()->get();

        return view('app.payments.create', compact('members', 'memberships'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-payments');

        $data = $request->validate([
            'member_id' => ['required', 'exists:tenant.members,id'],
            'membership_id' => ['nullable', 'exists:tenant.memberships,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:'.implode(',', Payment::METHODS)],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'paid_at' => ['required', 'date'],
        ]);

        Payment::create($data);

        return redirect()->route('app.payments.index')
            ->with('flash_success', 'Payment recorded.');
    }

    public function destroy(string $payment)
    {
        $this->authorize('manage-payments');

        Payment::findOrFail($payment)->delete();

        return back()->with('flash_success', 'Payment removed.');
    }
}
