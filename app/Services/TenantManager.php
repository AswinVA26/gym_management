<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantManager
{
    /**
     * The dedicated DB connection name used for the active tenant's database.
     */
    public const TENANT_CONNECTION = 'tenant';

    protected ?Tenant $tenant = null;

    /**
     * Activate a tenant (and switch its dedicated DB connection) for this request.
     */
    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;

        if ($tenant) {
            $this->configureConnection($tenant);
        }
    }

    /**
     * Deactivate tenancy and return to the platform (default) connection.
     */
    public function reset(): void
    {
        $this->tenant = null;
    }

    /**
     * The currently active tenant, if any.
     */
    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Whether a tenant database is active on this request.
     */
    public function ready(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * The DB connection name tenant-scoped models should use right now.
     */
    public function connectionName(): string
    {
        return $this->ready()
            ? static::TENANT_CONNECTION
            : (string) config('database.default');
    }

    /**
     * The active tenant's database connection instance.
     */
    public function connection(): Connection
    {
        return DB::connection($this->connectionName());
    }

    /**
     * Register (or refresh) the dynamic "tenant" database connection.
     */
    protected function configureConnection(Tenant $tenant): void
    {
        $driver = (string) config('database.default');

        $config = match ($driver) {
            'mysql', 'mariadb' => [
                'driver' => 'mysql',
                'host' => $tenant->db_host ?? (config("database.connections.$driver.host") ?: config('database.connections.mysql.host')),
                'port' => $tenant->db_port ?? (config("database.connections.$driver.port") ?: config('database.connections.mysql.port')),
                'database' => $tenant->db_name,
                'username' => $tenant->db_username ?? (config("database.connections.$driver.username") ?: config('database.connections.mysql.username')),
                'password' => $tenant->db_password ?? (config("database.connections.$driver.password") ?: config('database.connections.mysql.password')),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
            ],
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => $tenant->db_name,
                'prefix' => '',
                'foreign_key_constraints' => true,
                'busy_timeout' => 5000,
                'transaction_mode' => 'DEFERRED',
            ],
            default => array_merge(
                (array) Config::get('database.connections.'.$driver, []),
                ['database' => $tenant->db_name],
            ),
        };

        Config::set('database.connections.'.static::TENANT_CONNECTION, $config);
        DB::purge(static::TENANT_CONNECTION);
    }
}
