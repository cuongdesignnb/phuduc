<?php

namespace App\Http\Middleware;

use App\Services\Storefront\SiteConfigurationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchEngineVisibilityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            app(SiteConfigurationService::class)->get()['prevent_indexing'] ?? false
            || $this->hasUnsafeProductionConfiguration()
        ) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        } elseif ($this->isPrivatePath($request)) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    private function isPrivatePath(Request $request): bool
    {
        return $request->is([
            'login', 'register', 'forgot-password', 'reset-password/*',
            'verify-email', 'verify-email/*', 'confirm-password', 'password',
            'dashboard', 'profile', 'profile/*', 'admin', 'admin/*',
        ]);
    }

    private function hasUnsafeProductionConfiguration(): bool
    {
        return app()->environment('production')
            && (! str_starts_with((string) config('app.url'), 'https://')
                || strcasecmp((string) config('app.name'), 'Laravel') === 0);
    }
}
