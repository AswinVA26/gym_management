<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use Illuminate\Http\Request;

class PlatformSettingsController extends Controller
{
    public function __construct(
        protected AiService $ai,
    ) {}

    public function index()
    {
        return view('platform.settings.index', [
            'aiProviderLabel' => $this->ai->providerLabel(),
            'aiConfigured' => $this->ai->configured(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:10', 'confirmed'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => $data['password']]);
        }

        return back()->with('flash_success', 'Platform settings updated.');
    }
}