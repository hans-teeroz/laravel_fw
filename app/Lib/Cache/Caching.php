<?php

namespace App\Lib\Cache;

use App\Models\Base;
use Illuminate\Http\Request;
use Predis\Client;

class Caching
{
    protected $redis;

    protected $requestPathInfo;

    public function __construct($host = '127.0.0.1', $port = 6379)
    {
        $this->redis = new Client([
            'host' => env('REDIS_HOST') ?? $host,
            'port' => env('REDIS_PORT') ?? $port,
        ]);
    }

    public function setCache($key, $value, $auth = null, $model_name = '')
    {
        if ($key instanceof Request) {
            $key = $this->getKeyCache($key, $auth, $model_name);
        }
        $this->redis->set($key, $value, 'EX', 604800);
    }

    public function getCache($key)
    {
        return $this->redis->get($key);
    }

    public function getKeyCache(Request $request, $auth = null, $model_name = '')
    {
        $this->requestPathInfo = $request->route()->getPrefix();
        $requestUri = $request->query();
        $requestUriEncode = base64_encode(json_encode($requestUri));
        $key = $this->requestPathInfo . ':' . $requestUriEncode;
        if ($auth instanceof Base) {
            $key = $this->requestPathInfo . ':' . $auth->getKey() . ':' . $requestUriEncode;
        }
        if ($model_name !== '') {
            $key = base64_encode($model_name) . ':' . $key;
        }
        return env('REDIS_DATABASE') . ':' . $key;
    }

    public function deleteCache($pathInfo, $model_name = null, $relation_names = null)
    {
        $count = 0;
        if (isset($model_name) && $model_name !== '') {
            $pathInfo = env('REDIS_DATABASE') . ':' . base64_encode($model_name) . ':' . $pathInfo;
        }
        $keys = $this->redis->keys("$pathInfo*");
        if (is_array($relation_names) && count($relation_names) > 0) {
            foreach ($relation_names as $relation_name) {
                $keys = array_merge($keys, $this->redis->keys(env('REDIS_DATABASE') . ':' . "$relation_name*"));
            }
        }
        if (count($keys) > 0) {
            $count = $this->redis->del($keys);
        }
        return $count;
    }

    public function getAllKeyRedis()
    {
        return $this->redis->keys('*');
    }

    public function getKeyRedis($key)
    {
        return $this->redis->keys($key);
    }

    public function searchInKeyRedis($str_child, $str_parent)
    {
        $pos = strpos($str_parent, $str_child);
        if ($pos !== false) {
            return true;
        }
        else {
            return false;
        }
    }
}
