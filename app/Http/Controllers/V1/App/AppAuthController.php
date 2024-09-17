<?php

namespace App\Http\Controllers\V1\App;

use App\Http\Controllers\AuthController;
use App\Http\Requests\App\LoginRequest;
use App\Http\Requests\App\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group App - Authentication
 *
 * APIs for managing users
 */
class AppAuthController extends AuthController
{
    /**
     * @return string
     */
    protected function setMiddleware(): string
    {
        return "users:api";
    }

    /**
     * @return array
     */
    protected function setCustomClaims(): array
    {
        $user = get_auth('users:api');
        if (isset($user)) {
            $data = [
                'user' => [
                    "id"            => $user->id,
                    "username"      => $user->username,
                    "first_name"    => $user->first_name,
                    "last_name"     => $user->last_name,
                    "active"        => $user->active,
                    "email"         => $user->email,
                    "phone"         => $user->phone,
                    "address"       => $user->address,
                    "role"          => $user->role,
                    "enterprise_id" => $user->enterprise_id,
                    "parent_id"     => $user->parent_id,
                ]
            ];
        }
        return $data ?? [];
    }

    /**
     * Register new user
     **/
    public function _register(RegisterRequest $request): JsonResponse
    {
        return parent::__register($request);
    }

    /**
     * Login user
     **/
    public function _login(LoginRequest $request): JsonResponse
    {
        return parent::__login($request);
    }
}
