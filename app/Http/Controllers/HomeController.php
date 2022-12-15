<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return [
            'status'  => true,
            'success' => 'API running!'
        ];
    }
}
