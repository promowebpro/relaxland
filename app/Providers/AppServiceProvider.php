<?php

namespace App\Providers;

use App\Domain\Leads\Lead;
use App\Domain\Leads\LeadConsentDocument;
use App\Domain\Leads\LeadPolicy;
use App\Domain\Settings\SettingsRepository;
use App\Domain\Users\Enums\RoleName;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
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
        $this->app->singleton(
            SettingsRepository::class,
            fn ($app) => new SettingsRepository($app->make(CacheRepository::class)),
        );
        $this->app->scoped(LeadConsentDocument::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Lead::class, LeadPolicy::class);

        RateLimiter::for('lead-submissions', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));

        Gate::before(function (User $user, string $ability): ?bool {
            return str_contains($ability, '.') && $user->hasRole(RoleName::SuperAdmin->value)
                ? true
                : null;
        });
    }
}
