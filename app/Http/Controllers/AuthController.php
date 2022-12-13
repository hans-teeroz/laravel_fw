<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;


abstract class AuthController extends Controller
{
    protected $typeMiddleware;
    // protected $customClaims;

    public function __construct()
    {
        $this->typeMiddleware = $this->setMiddleware();
        // $this->customClaims = $this->setCustomClaims();
    }

    abstract protected function setMiddleware(): string;
    abstract protected function setCustomClaims(): array;

    public function __regitster(Request $request)
    {

        try {
            $result = services()->userService()->create([
                'username' => $request->username,
                'password' => $request->password,
                'email'    => $request->email,
                //TODO: device_id
            ]);
            return [
                'status'  => $result->status,
                'success' => trans('messages.successfully_register'),
                // 'data'    => $result->model
            ];

        }
        catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }

    }

    /**
     * Login
     *
     */
    public function __login(Request $request)
    {
        try {
            $credentials = $request->only('username', 'password');
            if (!$token = Auth::guard($this->typeMiddleware)->attempt($credentials, true)) {
                return response()->json(
                    ['status'  => false, 'message' => 'Unauthorized'],
                    401
                );
            }
            //TODO: Limit account login for devices
            $payload = JWTFactory::data($this->setCustomClaims())->make();
            // $payload = JWTFactory::data($this->setCustomClaims())->foo(['bar' => 'baz'])->make();
            $token = JWTAuth::fromUser(auth($this->typeMiddleware)->user(), $payload);
            return $this->createNewToken($token);
        }
        catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'status'       => true,
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => auth($this->typeMiddleware)->factory()->getTTL() * 60,
        ]);
    }

    /**
     * Get me
     *
     * @authenticated
     */
    public function __me()
    {
        try {
            // $token = JWTAuth::getToken();
            // $payload = JWTAuth::getPayload($token);
            $user = auth($this->typeMiddleware)->user();
            if ($user) {
                return $this->setCustomClaims();
            }
            return response()->json(
                ['status'  => false, 'message' => 'User Not Found'],
                404
            );
        }
        catch (TokenExpiredException $e) {
            return response()->json(
                ['status'  => false, 'message' => 'Token Expired'],
                500
            );
        }
        catch (TokenInvalidException $e) {
            return response()->json(
                ['status'  => false, 'message' => 'Token Invalid'],
                500
            );
        }
        catch (JWTException $e) {
            return response()->json(
                ['status'  => false, 'message' => $e->getMessage()],
                500
            );
        }
        catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * Refresh a token.
     *
     * @authenticated
     */
    public function __refresh()
    {
        $token = auth($this->typeMiddleware)->refresh();

        return $this->createNewToken($token);
    }
}
