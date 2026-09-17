<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AiService;
use App\Services\TenantManager;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        protected TenantManager $tenants,
        protected AiService $ai,
    ) {}

    protected function currentTenant()
    {
        abort_unless($this->tenants->ready(), 403, 'No gym workspace active.');

        return $this->tenants->get();
    }

    public function index()
    {
        $tenant = $this->currentTenant();

        $staff = User::where('tenant_id', $tenant->id)->latest()->get();

        return view('app.settings.index', [
            'tenant' => $tenant,
            'staff' => $staff,
            'aiProviderLabel' => $this->ai->providerLabel(),
            'aiConfigured' => $this->ai->configured(),
        ]);
    }

    public function storeStaff(Request $request)
    {
        abort_unless($request->user()->isGymAdmin() || $request->user()->isSuperAdmin(), 403);

        $tenant = $this->currentTenant();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10'],
            'role' => ['required', 'in:gym_admin,trainer'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'tenant_id' => $tenant->id,
            'role' => $data['role'],
            'status' => true,
        ]);

        return back()->with('flash_success', 'Staff account created.');
    }

    public function updateStaff(Request $request, User $user)
    {
        abort_unless($request->user()->isGymAdmin() || $request->user()->isSuperAdmin(), 403);

        $tenant = $this->currentTenant();
        abort_unless((int) $user->tenant_id === (int) $tenant->id, 403);
        abort_if($user->id === $request->user()->id, 422, 'You cannot edit your own role/status here.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:gym_admin,trainer'],
            'status' => ['boolean'],
            'password' => ['nullable', 'string', 'min:10'],
        ]);

        $user->update([
            'name' => $data['name'],
            'role' => $data['role'],
            'status' => $request->boolean('status'),
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => $data['password']]);
        }

        return back()->with('flash_success', 'Staff account updated.');
    }

    public function destroyStaff(Request $request, User $user)
    {
        abort_unless($request->user()->isGymAdmin() || $request->user()->isSuperAdmin(), 403);

        $tenant = $this->currentTenant();
        abort_unless((int) $user->tenant_id === (int) $tenant->id, 403);
        abort_if($user->id === $request->user()->id, 422, 'You cannot delete your own account.');

        $user->delete();

        return back()->with('flash_success', 'Staff account removed.');
    }
}
