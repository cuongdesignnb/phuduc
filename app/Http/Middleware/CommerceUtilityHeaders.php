<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CommerceUtilityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request)->withHeaders([
            'Cache-Control' => 'private, no-store',
            'Pragma' => 'no-cache',
            'Referrer-Policy' => 'no-referrer',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
