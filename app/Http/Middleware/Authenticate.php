<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // Return null to force JSON 401 for API requests
        if ($request->is('api/*')) {
            return null;
        }

        return null; // or '/login' if you want web redirect
    }
}
