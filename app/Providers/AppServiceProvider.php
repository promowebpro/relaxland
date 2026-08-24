<?php

namespace App\Providers;

use App\Domain\Genplan\Genplan;
use App\Domain\Genplan\GenplanContentPolicy;
use App\Domain\Genplan\InfrastructurePoint;
use App\Domain\Genplan\Plot;
use App\Domain\Genplan\PlotPolicy;
use App\Domain\Genplan\Quarter;
use App\Domain\Genplan\SurroundingPlace;
use App\Domain\Leads\Lead;
use App\Domain\Leads\LeadConsentDocument;
use App\Domain\Leads\LeadPolicy;
use App\Domain\Seo\CanonicalUrl;
use App\Domain\Settings\SettingsRepository;
use App\Domain\Users\Enums\RoleName;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
    public function boot(CanonicalUrl $canonicalUrl): void
    {
        $publicUrl = $canonicalUrl->origin();
        $scheme = parse_url($publicUrl, PHP_URL_SCHEME);
        $host = parse_url($publicUrl, PHP_URL_HOST);

        if (in_array($scheme, ['http', 'https'], true) && is_string($host) && $host !== '') {
            URL::forceRootUrl(rtrim($publicUrl, '/'));
            URL::forceScheme($scheme);
        }

        Gate::policy(Genplan::class, GenplanContentPolicy::class);
        Gate::policy(Quarter::class, GenplanContentPolicy::class);
        Gate::policy(InfrastructurePoint::class, GenplanContentPolicy::class);
        Gate::policy(SurroundingPlace::class, GenplanContentPolicy::class);
        Gate::policy(Plot::class, PlotPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);

        RateLimiter::for('lead-submissions', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));

        Gate::before(function (User $user, string $ability): ?bool {
            return str_contains($ability, '.') && $user->hasRole(RoleName::SuperAdmin->value)
                ? true
                : null;
        });
    }
}
