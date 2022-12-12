<?php

namespace App\Http\Controllers\V1\App;

use App\Http\Controllers\AuthController;
use App\Http\Requests\App\RegisterRequest;
use Illuminate\Http\Request;

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
        return $data;
    }

    public function _regitster(RegisterRequest $request)
    {
        return parent::__regitster($request);
    }
}
