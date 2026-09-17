<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function index()
    {
        $this->authorize('manage-trainers');

        $trainers = Trainer::withCount('members')->latest()->get();

        return view('app.trainers.index', compact('trainers'));
    }

    public function create()
    {
        $this->authorize('manage-trainers');

        return view('app.trainers.create');
    }

    public function store(Request $request)
    {
        $this->authorize('manage-trainers');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:tenant.trainers,email'],
            'phone' => ['required', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['boolean'],
        ]);

        Trainer::create($data);

        return redirect()->route('app.trainers.index')
            ->with('flash_success', 'Trainer added successfully.');
    }

    public function show(string $trainer)
    {
        $this->authorize('manage-trainers');

        $trainer = Trainer::with('members')->findOrFail($trainer);

        return view('app.trainers.show', compact('trainer'));
    }

    public function edit(string $trainer)
    {
        $this->authorize('manage-trainers');

        $trainer = Trainer::findOrFail($trainer);

        return view('app.trainers.edit', compact('trainer'));
    }

    public function update(Request $request, string $trainer)
    {
        $this->authorize('manage-trainers');

        $trainer = Trainer::findOrFail($trainer);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:tenant.trainers,email,'.$trainer->id],
            'phone' => ['required', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['boolean'],
        ]);

        $trainer->update($data);

        return redirect()->route('app.trainers.show', $trainer)
            ->with('flash_success', 'Trainer updated.');
    }

    public function destroy(string $trainer)
    {
        $this->authorize('manage-trainers');

        Trainer::findOrFail($trainer)->delete();

        return redirect()->route('app.trainers.index')
            ->with('flash_success', 'Trainer removed.');
    }
}
