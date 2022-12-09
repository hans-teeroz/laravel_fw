<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest;
use App\Lib\Cache\Caching;
use App\Services\ApiService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class ApiController extends BaseController
{
    protected $redis;

    public function __construct()
    {
        if (env('REDIS_ENABLE', false)) {
            $this->redis = new Caching();
        }
    }

    /**
     * @return ApiService
     */
    abstract protected function getService();

    public function getAuth()
    {
        if (strpos(\request()->route()->getPrefix(), 'app')) {
            return c('user_auth');
        }
        return c('admin_auth');
    }

    public function __list(Request $request)
    {
        if (env('REDIS_ENABLE', false)) {
            $result = $this->redis->getCache($this->redis->getKeyCache($request, $this->getAuth(), get_class($this->getService()->getModel())));
            if ($result) {
                return json_decode($result);
            }
            else {
                $result = $this->getService()->getMany($request);
                $this->redis->setCache($request, json_encode($result), $this->getAuth(), get_class($this->getService()->getModel()));
                return $result;
            }
        }
        else {
            $result = $this->getService()->getMany($request);
            return $result;
        }
    }

    public function __find(Request $request)
    {
        if (env('REDIS_ENABLE', false)) {
            $result = $this->redis->getCache($this->redis->getKeyCache($request, $this->getAuth(), get_class($this->getService()->getModel())));
            if ($result) {
                return json_decode($result);
            }
            else {
                $result = $this->getService()->getOne($request);
                $this->redis->setCache($request, json_encode($result), $this->getAuth(), get_class($this->getService()->getModel()));
                return $result;
            }
        }
        else {
            $result = $this->getService()->getOne($request);
            return $result;
        }
    }

    public function __create()
    {
        $data = $this->getCreatingData();
        $result = $this->getService()->create($data);
        if ($result->get('status')) {
            $model = $result->get('model');
            return response()->json([
                'status' => true,
                'data'   => $model instanceof Model ? $model : null,
            ], 201);
        }
        return response()->json([
            'status'  => false,
            'message' => $result->get('message'),
        ], 400);
    }

    public function __update($id)
    {
        $data = $this->getUpdatingData();
        $result = $this->getService()->update($id, $data, null);
        if ($result->get('status')) {
            $model = $result->get('model');
            return response()->json([
                'status' => true,
                'data'   => $model instanceof Model ? $model : null,
            ], 200);
        }
        return response()->json([
            'status'  => false,
            'message' => $result->get('message'),
        ], 400);
    }

    public function __delete($id)
    {
        $result = $this->getService()->delete($id, null);
        if ($result->get('status')) {
            return response()->json([
                'status' => true,
            ], 200);
        }
        return response()->json([
            'status'  => false,
            'message' => $result->get('message'),
        ], 400);
    }

    abstract protected function getRequest(): Request;

    protected function getCreatingData()
    {
        $request = $this->getRequest();
        if ($request instanceof BaseRequest) {
            $request->validated();
        }
        return $this->getJsonData();
    }

    protected function getUpdatingData()
    {
        $request = $this->getRequest();
        if ($request instanceof BaseRequest) {
            $request->validated();
        }
        return $this->getJsonData();
    }
}
