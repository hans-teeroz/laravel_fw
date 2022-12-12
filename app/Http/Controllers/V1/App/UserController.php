<?php

namespace App\Http\Controllers\V1\App;

use App\Http\Controllers\ApiController;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    /**
     * @inheritDoc
     */
    protected function getService(): UserService
    {
        return services()->userService();
    }
    /**
     * @return \Illuminate\Http\Request
     */
    protected function getRequest(): Request
    {
        return request();
    }
}
