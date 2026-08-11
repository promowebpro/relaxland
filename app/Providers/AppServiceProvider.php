<?php

namespace App\Providers;

use App\Domain\Settings\SettingsRepository;
use App\Domain\Users\Enums\RoleName;
use App\Models\User;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            SettingsRepository::class,
            fn ($app) => new SettingsRepository($app->make(CacheRepository::class)),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            return str_contains($ability, '.') && $user->hasRole(RoleName::SuperAdmin->value)
                ? true
                : null;
        });
    }
}
