<?php

namespace App\Http\Middleware\App;

use App\Http\Middleware\ApiAuthenticate;
use Closure;
use Illuminate\Http\Request;

class UserMiddleware extends ApiAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        return parent::handle($request, $next);
    }
}