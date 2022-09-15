<?php

namespace App\Lib\Helper;

class Result
{
    protected $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function get($key)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }

    public function set($key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function remove($key): Result
    {
        unset($this->data[$key]);

        return $this;
    }

    public function clean(): Result
    {
        $this->data = [];

        return $this;
    }

    public function clone(): Result
    {
        return new static($this->data);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value): void
    {
        $this->get($key, $value);
    }
}
