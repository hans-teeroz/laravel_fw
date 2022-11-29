<?php

namespace App\Http\Controllers;

use App\Services\UserService;

class UserController extends ApiController
{
    /**
     * @inheritDoc
     */
    protected function getService(): UserService
    {
        return services()->userService();
    }
}
