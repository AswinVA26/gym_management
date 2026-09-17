<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Trainer;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage-members');

        $members = Member::query()
            ->with('trainer')
            ->when($request->filled('q'), fn ($q) => $q->search((string) $request->input('q')))
            ->when($request->filled('status') && $request->input('status') !== 'all', fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('app.members.index', compact('members'));
    }

    public function create()
    {
        $this->authorize('manage-members');

        $trainers = Trainer::active()->get();

        return view('app.members.create', compact('trainers'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-members');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:tenant.members,email'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'dob' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'trainer_id' => ['nullable', 'exists:tenant.trainers,id'],
        ]);

        $data['member_code'] = $this->uniqueMemberCode();

        Member::create($data);

        return redirect()->route('app.members.index')
            ->with('flash_success', 'Member added successfully.');
    }

    public function show(Request $request, string $member)
    {
        $this->authorize('manage-members');

        $member = Member::with(['trainer', 'memberships.plan', 'payments', 'workoutPlans'])->findOrFail($member);

        return view('app.members.show', compact('member'));
    }

    public function edit(Request $request, string $member)
    {
        $this->authorize('manage-members');

        $member = Member::findOrFail($member);
        $trainers = Trainer::active()->get();

        return view('app.members.edit', compact('member', 'trainers'));
    }

    public function update(Request $request, string $member)
    {
        $this->authorize('manage-members');

        $member = Member::findOrFail($member);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:tenant.members,email,'.$member->id],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'dob' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'trainer_id' => ['nullable', 'exists:tenant.trainers,id'],
        ]);

        $member->update($data);

        return redirect()->route('app.members.show', $member)
            ->with('flash_success', 'Member updated.');
    }

    public function destroy(string $member)
    {
        $this->authorize('manage-members');

        Member::findOrFail($member)->delete();

        return redirect()->route('app.members.index')
            ->with('flash_success', 'Member removed.');
    }

    protected function uniqueMemberCode(): string
    {
        do {
            $code = Member::generateMemberCode();
        } while (Member::where('member_code', $code)->exists());

        return $code;
    }
}
