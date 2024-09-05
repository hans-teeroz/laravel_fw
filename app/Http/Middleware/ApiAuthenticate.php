<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class ApiAuthenticate
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
        $refreshToken = ($request->hasHeader('X-Refresh-Token')) ? $request->header('X-Refresh-Token') : null;
        if ($refreshToken) {
            preg_match("/Bearer ([^\ ]*)/i", $refreshToken, $match);
            $token = $match[1] ?? null;
            $refreshToken = Crypt::decryptString($token);
            $user = JWTAuth::setToken($refreshToken)->authenticate();

            App::singleton('old_refresh_token', function () use ($refreshToken) {
                return $refreshToken;
            });
            $this->verifyToken($user, $request);
        } else {
            try {
                $user = JWTAuth::parseToken()->authenticate();
                $this->verifyToken($user, $request);
            } catch (\Throwable $th) {
                return response()->json([
                    'status'  => false,
                    'message' => $th->getMessage()
                ], Response::HTTP_UNAUTHORIZED);
            }

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

    private function verifyToken(User $user, Request $request)
    {

        try {
            // Dùng để + thêm time cho acess token
            // JWTAuth::manager()->setRefreshFlow();
            // JWTAuth::factory()->setTTL(env('JWT_TTL'));
            if (!$user) {
                return $this->errorJsonUnauthorized(Response::HTTP_UNAUTHORIZED);
            }
            $request->setUserResolver(function () {
                return get_auth('users:api');
            });
        }
        catch (JWTException $e) {
            return $this->errorJson($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}