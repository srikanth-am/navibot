<?php

namespace App\Http\Middleware;

use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next, $role, $permission = null)
    {
        $roles = ["Admin", "User"];
        // echo $role;
        // die();
        $role_id = $request->user()->role_id;
        if ($roles[$role_id - 1] != $role) {
            abort(404);
        }
        return $next($request);
    }
}