<?php

namespace App\Http\Requests\App;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends BaseRequest
{

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'username'   => 'required|email',
            'password'   => 'required|min:4',
            'device_id' => 'required',
        ];
    }
}
