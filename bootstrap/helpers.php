<?php

use App\Lib\Helper\MapService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

if (function_exists('c')) {
    throw new Exception('function "c" is already existed !');
} else {
    function c(string $key)
    {
        return App::make($key);
    }
}


if (!function_exists('services')) {
    function services(): MapService
    {
        return c(MapService::class);
    }
}


if (!function_exists('convert_time')) {
    function convert_time($stringDate, $from, $to)
    {
        try {
            $date = new DateTime($stringDate, new DateTimeZone($from));
            $date->setTimezone(new DateTimeZone($to));
            return $date;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

if (!function_exists('get_data_user')) {
    function get_data_user($type, $field = 'id')
    {
        return Auth::guard($type)->user() ? Auth::guard($type)->user()->$field : '';
    }
}

if (!function_exists('get_auth')) {
    function get_auth($type)
    {
        return Auth::guard($type)->user() ? Auth::guard($type)->user() : null;
    }
}
