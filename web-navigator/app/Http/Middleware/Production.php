<?php

namespace App\Http\Middleware;

use Closure;

class Production
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
        $roleIds = [1,2];
        //
        $role_id = $request->user()->role_id;
        if (!in_array($role_id, $roleIds)) {
            abort(404);
        }
        return $next($request);
    }
}
