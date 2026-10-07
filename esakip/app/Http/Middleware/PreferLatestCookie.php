<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreferLatestCookie
{
    // Proxies with partitioned cookie jars may send duplicate names; use the newest (last) value.
    public function handle(Request $request, Closure $next)
    {
        $raw = $request->headers->get('Cookie');
        if ($raw && substr_count($raw, '=') > count($request->cookies->all())) {
            foreach (explode(';', $raw) as $pair) {
                [$k, $v] = array_pad(explode('=', trim($pair), 2), 2, '');
                if ($k !== '') {
                    $request->cookies->set(str_replace(['.', ' '], '_', urldecode($k)), urldecode($v));
                }
            }
        }

        return $next($request);
    }
}
