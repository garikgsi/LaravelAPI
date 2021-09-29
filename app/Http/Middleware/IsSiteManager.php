<?php

namespace App\Http\Middleware;

use Closure;
use App\Providers\RouteServiceProvider;


class IsSiteManager
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
        // $user = Auth::user();
        // var_dump($user->is_admin);
        if (!auth()->user()->is_site_manager) {
            return redirect(RouteServiceProvider::HOME);
            // dd($user);
        } else {
            return $next($request);
        }
    }
}
