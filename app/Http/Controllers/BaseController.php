<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;

abstract class BaseController extends Controller
{
    protected $jsonData;

    protected function getJsonData(string $key = null, $default = null)
    {
        if (!$this->jsonData) {
            $request = App::make(Request::class);
            $this->jsonData = json_decode($request->getContent(), true);
            if (!is_array($this->jsonData)) {
                $this->jsonData = [];
            }
        }
        if (is_null($key)) {
            return $this->jsonData;
        }

        return isset($this->jsonData[$key]) ? $this->jsonData[$key] : $default;
    }

    protected function getQueryParam(string $key = null, $default = null)
    {
        $request = App::make(Request::class);
        if ($key === null) {
            return $request->query->all();
        }
        return $request->query->get($key, $default);
    }

    protected function getFilter()
    {
        $raw = $this->getQueryParam('_filter', null);
        $data = [];
        $raws = array_filter(explode(';', $raw));
        foreach ($raws as $item) {
            $items = explode(':', $item);
            if (is_array($items) && count($items) >= 2) {
                $data[array_shift($items)] = implode(':', $items);
            }
        }
        return $data;
    }
}
