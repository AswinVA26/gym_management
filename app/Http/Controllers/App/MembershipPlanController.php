<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    public function index()
    {
        $this->authorize('manage-plans');

        $plans = MembershipPlan::withCount('memberships')->latest()->get();

        return view('app.plans.index', compact('plans'));
    }

    public function create()
    {
        $this->authorize('manage-plans');

        return view('app.plans.create');
    }

    public function store(Request $request)
    {
        $this->authorize('manage-plans');

        $data = $request->validate([
            'plan_name' => ['required', 'string', 'max:255'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['boolean'],
        ]);

        MembershipPlan::create($data);

        return redirect()->route('app.plans.index')
            ->with('flash_success', 'Membership plan created.');
    }

    public function edit(string $plan)
    {
        $this->authorize('manage-plans');

        $plan = MembershipPlan::findOrFail($plan);

        return view('app.plans.edit', compact('plan'));
    }

    public function update(Request $request, string $plan)
    {
        $this->authorize('manage-plans');

        $plan = MembershipPlan::findOrFail($plan);

        $data = $request->validate([
            'plan_name' => ['required', 'string', 'max:255'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['boolean'],
        ]);

        $plan->update($data);

        return redirect()->route('app.plans.index')
            ->with('flash_success', 'Membership plan updated.');
    }

    public function destroy(string $plan)
    {
        $this->authorize('manage-plans');

        MembershipPlan::findOrFail($plan)->delete();

        return redirect()->route('app.plans.index')
            ->with('flash_success', 'Membership plan removed.');
    }
}
