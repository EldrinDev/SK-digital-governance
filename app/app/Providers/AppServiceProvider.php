<?php

namespace App\Providers;

use App\BackupManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BackupManager::class, fn () => BackupManager::forApplication());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination.sk');

        // Flash a toast after an action: redirect()->...->toast('Announcement taken down.', 'warning').
        // Kinds: success (the default), info, warning and error.
        RedirectResponse::macro('toast', function (string $message, string $kind = 'success'): RedirectResponse {
            /** @var RedirectResponse $this */
            return $this->with('status', $message)->with('status_kind', $kind);
        });

        // Youth forms (registration, feedback, surveys) are the platform's spam surface.
        // Reading pages stays unlimited; only submissions are counted.
        RateLimiter::for('public-forms', fn (Request $request) => $request->isMethodSafe()
            ? Limit::none()
            : Limit::perMinute(15)->by($request->user()?->id ?: $request->ip()));
    }
}
