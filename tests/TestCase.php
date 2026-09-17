<?php

namespace Tests;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantManager;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Physical sqlite paths created for tenants during the test, so they can be
     * removed afterwards and never leak into other tests.
     *
     * @var array<int, string>
     */
    protected array $tenantDatabasePaths = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->discardTenantConnection();
        $this->tenantDatabasePaths = [];
    }

    protected function tearDown(): void
    {
        $this->discardTenantConnection();

        foreach ($this->tenantDatabasePaths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    /**
     * Release any handle to the dynamic tenant connection so SQLite files are
     * not locked and can be deleted (required on Windows).
     */
    protected function discardTenantConnection(): void
    {
        try {
            DB::purge(TenantManager::TENANT_CONNECTION);
        } catch (\Throwable) {
            // connection may already be gone; nothing to do
        }
    }

    protected function makeSuperAdmin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    /**
     * Provision a gym: creates the physical database, runs tenant migrations
     * and persists the tenant record.
     */
    protected function provisionGym(string $slug = 'alphafitness', string $name = 'Alpha Fitness'): Tenant
    {
        $tenant = app(TenantService::class)->create([
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
        ]);

        $this->tenantDatabasePaths[] = $tenant->db_name;

        return $tenant;
    }

    protected function gymAdminFor(Tenant $tenant): User
    {
        return User::factory()->gymAdmin($tenant)->create();
    }

    protected function trainerFor(Tenant $tenant): User
    {
        return User::factory()->trainer($tenant)->create();
    }

    /**
     * Activate a tenant on the shared (singleton) manager so direct model
     * queries in the test body target the gym database.
     */
    protected function switchToGym(Tenant $tenant): void
    {
        app(TenantManager::class)->set($tenant);
    }
}
