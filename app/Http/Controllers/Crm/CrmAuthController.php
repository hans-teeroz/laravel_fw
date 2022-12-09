<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\AuthController;

class CrmAuthController extends AuthController
{
    /**
     * @return string
     */
    protected function setMiddleware(): string
    {
        return "";
    }

    /**
     * @return array
     */
    protected function setCustomClaims(): array
    {
        return [];
    }
}
