<?php

namespace App\Http\Controllers\V1\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrmHomeController extends Controller
{
    public function index()
    {
        return [
            'status'  => true,
            'success' => 'Crm API running!'
        ];
    }
}
