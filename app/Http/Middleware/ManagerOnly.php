<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ManagerOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user() && $request->user()->role === 'manager', 403, 'เฉพาะผู้จัดการเท่านั้น');

        return $next($request);
    }
}