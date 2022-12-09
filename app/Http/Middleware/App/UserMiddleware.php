<?php

namespace App\Http\Middleware\App;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return $this->errorJsonUnauthorized(Response::HTTP_UNAUTHORIZED);
            }
            $request->setUserResolver(function () {
                return get_auth('users:api');
            });
        } catch (JWTException $e) {
            return $this->errorJson($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return $next($request);
    }


    protected function errorJsonUnauthorized($statusCode = Response::HTTP_BAD_REQUEST): Response
    {
        return new JsonResponse([
            'status'  => false,
            'message' => 'Unauthorized! Hey, who are you ?',
        ], $statusCode);
    }

    protected function errorJson($message, $statusCode = Response::HTTP_BAD_REQUEST): Response
    {
        return new JsonResponse([
            'status'  => false,
            'message' => $message,
        ], $statusCode);
    }
}