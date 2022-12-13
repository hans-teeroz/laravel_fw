<?php

namespace App\Http\Controllers\V1\App;

use App\Http\Controllers\ApiController;
use App\Http\Requests\App\UserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;


/**
 * @group App - User management
 *
 * APIs for managing users
 */
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
        return c(UserRequest::class);
    }


    /**
     *
     * Get list user
     *
     *
     * @authenticated
     * @urlParam lang The language. Example: en
     */
    public function __list(Request $request)
    {
        return parent::__list($request);
    }

    /**
     *
     * Create a new user
     *
     *
     * @authenticated
     * @bodyParam first_name string required The first name of the user. Example: John
     * @bodyParam last_name string required The last name of the user. Example: Wick
     * @bodyParam email email required Whether to ban the user forever. Example: john@gmail.com
     * @bodyParam username string required The username of the user to login. Example: johnwick
     * @bodyParam password string required The password of the user to login. Example: 1234
     * @bodyParam phone string The phone of the user. No-example
     * @bodyParam address string The address of the user to login. No-example
     * @bodyParam role string The role of the user. Defaults to 'guest'. Example: guest
     */
    public function __create()
    {
        return parent::__create();
    }
}
