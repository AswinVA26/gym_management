<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class TenantService
{
    /**
     * Database name prefix used for every gym database (e.g. "gym_ironworks").
     */
    public const DB_PREFIX = 'gym_';

    /**
     * Registry entries declaring the currently known tenants resolved by slug
     * for the active request. Kept so the setup middleware never re-queries.
     *
     * @var array<string, int>
     */
    protected static array $cache = [];

    public function __construct(
        protected TenantManager $manager,
    ) {}

    /**
     * Build the physical database name for a slug with strict validation.
     */
    public function databaseName(string $slug): string
    {
        $slug = Str::lower((string) Str::slug($slug, ''));

        if (! preg_match('/^[a-z][a-z0-9_]{2,30}$/i', $slug) || in_array($slug, ['laravel', 'mysql', 'information_schema', 'performance_schema'], true)) {
            throw new InvalidArgumentException('Slug may only contain lowercase letters, digits and underscores.');
        }

        return static::DB_PREFIX.$slug;
    }

    /**
     * Provision a brand new gym: create its physical database, run the
     * tenant schema, and persist the registry record.
     */
    public function create(array $attributes): Tenant
    {
        $slug = Str::lower((string) Str::slug((string) $attributes['slug'], ''));
        $dbName = $this->databaseName($slug);

        $tenant = new Tenant([
            'name' => $attributes['name'],
            'slug' => $slug,
            'domain' => $attributes['domain'] ?? null ?: null,
            'db_name' => $dbName,
            'db_host' => $attributes['db_host'] ?? null ?: null,
            'db_port' => $attributes['db_port'] ?? null ?: null,
            'db_username' => $attributes['db_username'] ?? null ?: null,
            'db_password' => $attributes['db_password'] ?? null ?: null,
            'settings' => $attributes['settings'] ?? null,
            'status' => $attributes['status'] ?? 'active',
        ]);

        try {
            $this->createDatabase($tenant);

            return DB::transaction(function () use ($tenant) {
                $tenant->save();
                $this->runMigrations($tenant);

                return $tenant;
            });
        } catch (Throwable $e) {
            $this->dropDatabase($tenant->db_name, $tenant);
            Log::error('Tenant provisioning failed', ['slug' => $slug, 'error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Execute the tenant schema migrations against a gym database.
     */
    public function runMigrations(Tenant $tenant): void
    {
        $this->manager->set($tenant);

        try {
            $exit = Artisan::call('migrate', [
                '--database' => TenantManager::TENANT_CONNECTION,
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            if ($exit !== 0) {
                throw new \RuntimeException('Tenant migration failed.');
            }
        } finally {
            $this->manager->reset();
        }
    }

    /**
     * Permanently remove a gym and its physical database.
     */
    public function delete(Tenant $tenant): void
    {
        $dbName = $tenant->db_name;

        $tenant->delete();

        $this->dropDatabase($dbName, $tenant);

        unset(static::$cache[$tenant->slug]);
    }

    /**
     * Look up a tenant by slug, remembering the result for the request.
     */
    public function findBySlug(string $slug): ?Tenant
    {
        $slug = Str::lower((string) Str::slug($slug, ''));

        if (array_key_exists($slug, static::$cache)) {
            return static::$cache[$slug] ? Tenant::find(static::$cache[$slug]) : null;
        }

        $tenant = Tenant::where('slug', $slug)->first();
        static::$cache[$slug] = $tenant?->id;

        return $tenant;
    }

    /**
     * Create the physical database. SQLite (used in tests) is a file on disk.
     */
    protected function createDatabase(Tenant $tenant): void
    {
        if ((string) config('database.default') === 'sqlite') {
            $path = database_path('tenants/'.$tenant->db_name.'.sqlite');

            $dir = dirname($path);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Drop any stale file left behind by an interrupted test/run so we
            // always provision a truly empty database.
            static::discardTenantConnection();
            if (is_file($path)) {
                @unlink($path);
            }

            touch($path);

            $tenant->db_name = $path;

            return;
        }

        DB::statement(
            'CREATE DATABASE IF NOT EXISTS `'.$tenant->db_name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
    }

    protected function dropDatabase(string $dbName, Tenant $tenant): void
    {
        static::discardTenantConnection();

        if ((string) config('database.default') === 'sqlite') {
            if (is_file($dbName)) {
                @unlink($dbName);
            }

            return;
        }

        // MariaDB / MySQL keep a session lock on an open PDO handle to the
        // database, so purge the tenant connection first.
        try {
            DB::statement('DROP DATABASE IF EXISTS `'.$dbName.'`');
        } catch (Throwable $e) {
            Log::warning('Could not drop gym database', ['db' => $dbName, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Drop any live handle to the dynamic tenant connection. Windows keeps a
     * lock on open SQLite files, so this must happen before deleting them.
     */
    protected static function discardTenantConnection(): void
    {
        try {
            DB::purge(TenantManager::TENANT_CONNECTION);
        } catch (Throwable $e) {
            Log::warning('Could not purge tenant connection', ['error' => $e->getMessage()]);
        }
    }
}
