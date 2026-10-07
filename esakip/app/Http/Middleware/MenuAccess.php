<?php

namespace App\Http\Middleware;

use App\Support\Esakip;
use Closure;
use Illuminate\Http\Request;

class MenuAccess
{
    public function handle(Request $request, Closure $next, string $key)
    {
        abort_unless(Esakip::canMenu($key), 403, 'Anda tidak memiliki akses ke menu ini.');

        return $next($request);
    }
}
