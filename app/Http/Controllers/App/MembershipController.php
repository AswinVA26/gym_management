<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MembershipController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage-memberships');

        Membership::where('status', Membership::STATUS_ACTIVE)
            ->where('end_date', '<', now()->toDateString())
            ->update(['status' => Membership::STATUS_EXPIRED]);

        $memberships = Membership::query()
            ->with('member', 'plan')
            ->when($request->filled('status') && $request->input('status') !== 'all', fn ($q) => $q->where('status', $request->input('status')))
            ->latest('start_date')
            ->paginate(12)
            ->withQueryString();

        return view('app.memberships.index', compact('memberships'));
    }

    public function create()
    {
        $this->authorize('manage-memberships');

        $members = Member::active()->get();
        $plans = MembershipPlan::active()->get();

        return view('app.memberships.create', compact('members', 'plans'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-memberships');

        $data = $request->validate([
            'member_id' => ['required', 'exists:tenant.members,id'],
            'plan_id' => ['required', 'exists:tenant.membership_plans,id'],
            'start_date' => ['required', 'date'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,expired,cancelled'],
        ]);

        $plan = MembershipPlan::findOrFail($data['plan_id']);

        $start = Carbon::parse($data['start_date']);
        $end = $start->copy()->addMonths((int) $plan->duration_months)->subDay();

        Membership::create([
            'member_id' => $data['member_id'],
            'plan_id' => $plan->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'price' => $data['price'] ?? $plan->price,
            'status' => $data['status'],
        ]);

        return redirect()->route('app.memberships.index')
            ->with('flash_success', 'Membership assigned.');
    }

    public function show(string $membership)
    {
        $this->authorize('manage-memberships');

        $membership = Membership::with('member', 'plan', 'payments')->findOrFail($membership);

        return view('app.memberships.show', compact('membership'));
    }

    public function edit(string $membership)
    {
        $this->authorize('manage-memberships');

        $membership = Membership::findOrFail($membership);
        $members = Member::all();
        $plans = MembershipPlan::active()->get();

        return view('app.memberships.edit', compact('membership', 'members', 'plans'));
    }

    public function update(Request $request, string $membership)
    {
        $this->authorize('manage-memberships');

        $membership = Membership::findOrFail($membership);

        $data = $request->validate([
            'member_id' => ['required', 'exists:tenant.members,id'],
            'plan_id' => ['required', 'exists:tenant.membership_plans,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,expired,cancelled'],
        ]);

        $membership->update($data);

        return redirect()->route('app.memberships.show', $membership)
            ->with('flash_success', 'Membership updated.');
    }

    public function destroy(string $membership)
    {
        $this->authorize('manage-memberships');

        Membership::findOrFail($membership)->delete();

        return redirect()->route('app.memberships.index')
            ->with('flash_success', 'Membership removed.');
    }

    public function cancel(string $membership)
    {
        $this->authorize('manage-memberships');

        $membership = Membership::findOrFail($membership);
        $membership->update(['status' => Membership::STATUS_CANCELLED]);

        return back()->with('flash_success', 'Membership cancelled.');
    }
}
