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

    public function document()
    {

        return view('document')->with('docs', \File::get(public_path() . '/docs/index.html'));
        // $html = view('admin::components.order', compact('orders'))->render();
        // return \File::get(resource_path() . '/views/docs/index.html');

    }
}
