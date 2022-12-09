<?php

namespace App\Http\Requests\App;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends BaseRequest
{

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'username' => 'required|email|unique:users,username,' . $this->id,
            'password' => 'required|min:4',
        ];
    }
}
