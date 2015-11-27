<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiController;
use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateApi
{
    /**
     * Check for a user using Basic Authentication. If this was not a demo task where an ability to quickly test
     * the code is somewhat more important than security, Oauth or JSON Web Tokens would be used
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Even though Basic Authentication is used for now, will not show a dialog for it.
        // Instead, will generate appropriate API response
        if (Auth::onceBasic()) {
            return (new ApiController)->respondWhenUnauthenticated();
        }

        return $next($request);
    }
}
