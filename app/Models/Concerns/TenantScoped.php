<?php

namespace App\Models\Concerns;

use App\Services\TenantManager;

/**
 * Routes a model's queries to the currently active tenant database,
 * guaranteeing per-gym physical data isolation on a shared codebase.
 */
trait TenantScoped
{
    /**
     * The database connection name used by this model.
     */
    public function getConnectionName(): ?string
    {
        return app(TenantManager::class)->connectionName();
    }
}
