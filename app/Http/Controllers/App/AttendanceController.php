<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage-attendance');

        $records = Attendance::query()
            ->with('member')
            ->when($request->filled('date'), fn ($q) => $q->whereDate('check_in', $request->input('date')))
            ->latest('check_in')
            ->paginate(15)
            ->withQueryString();

        $todayCount = Attendance::whereDate('check_in', now()->toDateString())->count();

        return view('app.attendance.index', compact('records', 'todayCount'));
    }

    public function create()
    {
        $this->authorize('manage-attendance');

        $members = Member::active()->get();

        return view('app.attendance.create', compact('members'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-attendance');

        $data = $request->validate([
            'member_id' => ['required', 'exists:tenant.members,id'],
            'check_in' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $alreadyOpen = Attendance::where('member_id', $data['member_id'])->whereNull('check_out')->exists();

        abort_if($alreadyOpen, 422, 'This member already has an open check-in.');

        Attendance::create([
            'member_id' => $data['member_id'],
            'check_in' => $data['check_in'] ? Carbon::parse($data['check_in']) : now(),
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('app.attendance.index')
            ->with('flash_success', 'Check-in recorded.');
    }

    public function edit(string $attendance)
    {
        $this->authorize('manage-attendance');

        $attendance = Attendance::findOrFail($attendance);
        $members = Member::active()->get();

        return view('app.attendance.edit', compact('attendance', 'members'));
    }

    public function update(Request $request, string $attendance)
    {
        $this->authorize('manage-attendance');

        $attendance = Attendance::findOrFail($attendance);

        $data = $request->validate([
            'member_id' => ['required', 'exists:tenant.members,id'],
            'check_in' => ['required', 'date'],
            'check_out' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $checkOut = ! empty($data['check_out']) ? Carbon::parse($data['check_out']) : null;

        abort_if(
            $checkOut !== null && Carbon::parse($data['check_in'])->gt($checkOut),
            422,
            'Check-out cannot be before check-in.'
        );

        $attendance->update([
            'member_id' => $data['member_id'],
            'check_in' => Carbon::parse($data['check_in']),
            'check_out' => $checkOut,
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('app.attendance.index')
            ->with('flash_success', 'Attendance record updated.');
    }

    public function checkout(Request $request, string $attendance)
    {
        $this->authorize('manage-attendance');

        $attendance = Attendance::findOrFail($attendance);

        if ($attendance->check_out !== null) {
            return back()->with('flash_warning', 'This member has already checked out.');
        }

        $data = $request->validate([
            'check_out' => ['required', 'date'],
        ]);

        $checkOut = Carbon::parse($data['check_out']);

        abort_if($attendance->check_in !== null && $checkOut->lt($attendance->check_in), 422, 'Check-out cannot be before check-in.');

        $attendance->update(['check_out' => $checkOut]);

        return back()->with('flash_success', 'Check-out recorded.');
    }

    public function destroy(string $attendance)
    {
        $this->authorize('manage-attendance');

        Attendance::findOrFail($attendance)->delete();

        return back()->with('flash_success', 'Attendance record removed.');
    }
}