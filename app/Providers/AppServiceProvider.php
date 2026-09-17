<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AiService;
use App\Services\TenantManager;
use App\Services\TenantService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantManager::class);
        $this->app->singleton(TenantService::class);
        $this->app->singleton(AiService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootGates();
        $this->bootRateLimiters();
    }

    protected function bootGates(): void
    {
        Gate::before(fn (?User $user) => $user?->isSuperAdmin() ? true : null);

        Gate::define('manage-members', fn (User $user) => $user->isGymAdmin() || $user->isTrainer());
        Gate::define('manage-trainers', fn (User $user) => $user->isGymAdmin());
        Gate::define('manage-plans', fn (User $user) => $user->isGymAdmin());
        Gate::define('manage-memberships', fn (User $user) => $user->isGymAdmin());
        Gate::define('manage-payments', fn (User $user) => $user->isGymAdmin());
        Gate::define('manage-attendance', fn (User $user) => $user->isGymAdmin() || $user->isTrainer());
        Gate::define('manage-staff', fn (User $user) => $user->isGymAdmin());
        Gate::define('use-ai', fn (User $user) => $user->isGymAdmin() || $user->isTrainer());
    }

    protected function bootRateLimiters(): void
    {
        RateLimiter::for('login', fn ($job) => Limit::perMinute(6)->by($job->input('email') ?: $job->ip()));
        RateLimiter::for('ai', fn ($job) => Limit::perMinute(30)->by($job->user()?->id ?: $job->ip()));
    }
}
