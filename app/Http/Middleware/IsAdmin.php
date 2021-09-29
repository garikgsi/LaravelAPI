<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

// use Illuminate\Support\Facades\Route;
// use Illuminate\Auth\Middleware\Authenticate as Middleware;



class IsAdmin
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
        if (!auth()->user()->is_admin) {
            return redirect(RouteServiceProvider::HOME);
            // dd($user);
        } else {
            return $next($request);
        }
    }
}
