<?php

namespace App\Services;

use App\Models\User;

class UserService extends ApiService
{
    protected $model = User::class;

    protected $relations = [];

    protected $fieldsName = '_user_fields';

    protected function getOrderbyableFields(): array
    {
        return ['id'];
    }

    protected function getFilterableFields(): array
    {
        return [];
    }

    protected function fields(): array
    {
        return ['first_name', 'last_name', 'email', 'password', 'username', 'active', 'phone', 'address', 'role', 'fullname'];
    }

    protected function mapFilters(): array
    {
        return [];
    }

    protected function newQuery()
    {

        $query = parent::newQuery();
        // dd($query->get());
        return $query;
    }

    protected function boot()
    {
        parent::boot();
        $this->on('creating', function ($model) {
            // dd($model);
        });
    }
}
