<?php

namespace App\Services;

use App\Lib\SupportTrait\ApiTrait;

abstract class ApiService extends BaseService
{
    use ApiTrait;

    protected function boot()
    {
        parent::boot();
    }
}
