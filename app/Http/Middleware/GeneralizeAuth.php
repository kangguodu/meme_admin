<?php

namespace App\Http\Middleware;

use Closure;

class GeneralizeAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        config(['jwt.user'=>'\App\Member']);
        config(['auth.providers.users.model'=>\App\Member::class]);

        return $next($request);
    }
}
