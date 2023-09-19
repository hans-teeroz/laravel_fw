<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest;
use App\Lib\Cache\Caching;
use App\Services\ApiService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Env;

abstract class ApiController extends BaseController
{
    protected $redis;

    protected $currentFunction;

    public function __construct()
    {
        if (env('REDIS_CACHE_ENABLE', false)) {
            $this->redis = new Caching();
            if (request()->method() !== "GET") {
                $currentFunction = explode('@', $this->getCurrentRoute());
                $this->currentFunction = array_pop($currentFunction);
                if (!in_array($this->currentFunction, ['__list', '__find', '__create', '__update', '__delete'])) {
                    $pathInfo = request()->route()->getPrefix();
                    //                    SyncDataJob::dispatch($pathInfo, get_class($this->getService()->getModel()), $this->getService()->getModel()->getEndcodeAllRelations());
                    $this->redis->deleteCache($pathInfo, get_class($this->getService()->getModel()), $this->getService()->getModel()->getEndcodeAllRelations());
                }
            }
        }
    }

    /**
     * @return ApiService
     */
    abstract protected function getService();

    public function getAuth()
    {
        return get_authed();
    }

    public function __list(Request $request)
    {
        if (env('REDIS_CACHE_ENABLE', false)) {
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
        if (env('REDIS_CACHE_ENABLE', false)) {
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