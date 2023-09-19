<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Lib\Cache\Caching;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redis;
use RuntimeException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;


abstract class AuthController extends Controller
{
    protected $typeMiddleware;
    protected $redis;
    // protected $customClaims;

    public function __construct()
    {
        $this->redis = new Caching();
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
            $device_id = $request->device_id;
            if (!$token = Auth::guard($this->typeMiddleware)->attempt($credentials, true)) {
                return response()->json(
                    ['status' => false, 'message' => 'Unauthorized'],
                    401
                );
            }

            //TODO: Limit account login for devices
            // $payload = JWTFactory::data($this->setCustomClaims())->make();
            $payload = JWTFactory::data($this->setCustomClaims())->device(['device_id' => $device_id])->make();
            $accessToken = JWTAuth::fromUser(auth($this->typeMiddleware)->user(), $payload);
            $refreshToken = Crypt::encryptString(auth($this->typeMiddleware)->setTTL(env('JWT_REFRESH_TTL'))->attempt($credentials, true));

            return $this->createNewToken($refreshToken, $accessToken, $device_id);
        }
        catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    protected function createNewToken($refreshToken, $token, $device_id)
    {
        $this->createRefreshToken(get_authed()->getKey(), $device_id, $refreshToken);
        return response()->json([
            'status'        => true,
            'access_token'  => $token,
            'token_type'    => 'Bearer',
            // 'expires_in'    => env('JWT_TTL'),
            'refresh_token' => $refreshToken
        ]);
    }

    protected function createRefreshToken($userId, $deviceId, $refreshToken)
    {
        $key = env('REDIS_DATABASE') . ":refresh_tokens:$this->typeMiddleware:$userId:$deviceId";
        $this->redis->setCache($key, $refreshToken, null, '', env('JWT_REFRESH_TTL'));
    }

    /**
     * Get me
     *
     * @authenticated
     */
    public function __me()
    {
        try {

            $token = JWTAuth::getToken();
            // $payload = JWTAuth::getPayload($token);
            $user = auth($this->typeMiddleware)->user();
            if ($user) {
                $data = $this->setCustomClaims();
                $data['access_token'] = $token->get();
                return response()->json([
                    'status'    => true,
                    'data'      => $data,
                ]);
            }
            return response()->json(
                ['status' => false, 'message' => 'User Not Found'],
                404
            );
        }
        catch (TokenExpiredException $e) {
            return response()->json(
                ['status' => false, 'message' => 'Token Expired'],
                401
            );
        }
        catch (TokenInvalidException $e) {
            return response()->json(
                ['status' => false, 'message' => 'Token Invalid'],
                401
            );
        }
        catch (JWTException $e) {
            return response()->json(
                ['status' => false, 'message' => $e->getMessage()],
                401
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
    public function __refresh(Request $request)
    {
        //TODO: refresh token current in blacklist + return new access token & new refresh token + update refresh token with redis
        try {
            $auth = auth($this->typeMiddleware);

            $payload = JWTFactory::data($this->setCustomClaims())->make();
            $accessToken = JWTAuth::fromUser($auth->user(), $payload);

            $oldRefreshToken = c('old_refresh_token');

            $pathInfo = env('REDIS_DATABASE') . ":refresh_tokens:$this->typeMiddleware:" . $auth->user()->getKey();
            $allRefreshTokenKeys = $this->redis->getKeyRedis("$pathInfo*");

            foreach ($allRefreshTokenKeys as $key => $value) {
                $token = $this->redis->getCache($value);
                if (Crypt::decryptString($token) === $oldRefreshToken) {
                    JWTAuth::manager()->setRefreshFlow();
                    JWTAuth::factory()->setTTL(env('JWT_REFRESH_TTL'));
                    $refreshToken = Crypt::encryptString(JWTAuth::fromUser($auth->user(), $payload));
                    $this->redis->setCache($value, $refreshToken, null, '', env('JWT_REFRESH_TTL'));
                    break;
                }
            }
            if (!isset($refreshToken)) {
                return response()->json(
                    ['status' => false, 'message' => 'Token Invalid'],
                    401
                );
            }
            return response()->json([
                'status'        => true,
                'access_token'  => $accessToken,
                'token_type'    => 'Bearer',
                'refresh_token' => $refreshToken
            ]);

        } catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }

    }
}