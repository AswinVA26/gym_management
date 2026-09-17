<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        protected TenantService $tenants,
    ) {}

    public function index()
    {
        $gyms = Tenant::withCount('users')->latest()->paginate(20);

        return view('platform.tenants.index', compact('gyms'));
    }

    public function create()
    {
        return view('platform.tenants.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:32', 'unique:tenants,slug'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:tenants,domain'],
            'status' => ['required', 'in:active,suspended'],
            'admin_name' => ['nullable', 'string', 'max:255', 'required_with:admin_email'],
            'admin_email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['nullable', 'string', 'min:10', 'required_with:admin_email'],
        ]);

        $tenant = $this->tenants->create($data);

        if (! empty($data['admin_email'])) {
            User::create([
                'name' => $data['admin_name'] ?? $data['name'].' Admin',
                'email' => $data['admin_email'],
                'password' => $data['admin_password'],
                'tenant_id' => $tenant->id,
                'role' => User::ROLE_GYM_ADMIN,
                'status' => true,
            ]);
        }

        return redirect()
            ->route('platform.tenants.index')
            ->with('flash_success', "Gym \"{$tenant->name}\" provisioned with its own isolated database.");
    }

    public function edit(Tenant $tenant)
    {
        return view('platform.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:tenants,domain,'.$tenant->id],
            'status' => ['required', 'in:active,suspended'],
        ]);

        $tenant->update($data);

        return redirect()
            ->route('platform.tenants.index')
            ->with('flash_success', 'Gym settings updated.');
    }

    public function destroy(Tenant $tenant)
    {
        $name = $tenant->name;
        $this->tenants->delete($tenant);

        return redirect()
            ->route('platform.tenants.index')
            ->with('flash_success', "Gym \"{$name}\" and its database were permanently removed.");
    }

    public function switchTo(Tenant $tenant)
    {
        abort_unless($tenant->isActive(), 403, 'This gym is suspended.');

        session(['tenant_override' => $tenant->id]);

        return redirect()->route('app.dashboard');
    }

    public function leave()
    {
        session()->forget('tenant_override');

        return redirect()->route('platform.index');
    }

    public function toggle(Tenant $tenant)
    {
        $tenant->update(['status' => $tenant->isActive() ? 'suspended' : 'active']);

        session()->forget('tenant_override');

        return redirect()->route('platform.tenants.index')
            ->with('flash_success', 'Gym status updated.');
    }
}
