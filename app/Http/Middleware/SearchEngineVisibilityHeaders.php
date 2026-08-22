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

        if (app(SiteConfigurationService::class)->get()['prevent_indexing'] ?? false) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
