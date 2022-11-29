<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest;
use App\Services\ApiService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class ApiController extends BaseController
{
    /**
     * @return ApiService
     */
    abstract protected function getService();

    public function __list(Request $request)
    {
        return $this->getService()->getMany($request);
    }

    public function __find(Request $request)
    {
        return $this->getService()->getOne($request);
    }

    public function __create()
    {
        $data = $this->getCreatingData();
        $result = $this->getService()->create($data);
        if ($result->get('status')) {
            $model = $result->get('model');
            return [
                'status' => true,
                'data' => $model instanceof Model ? $model : null,
            ];
        }
        return [
            'status' => false,
            'message' => $result->get('message'),
        ];
    }

    public function __update($id)
    {
        $data = $this->getUpdatingData();
        $result = $this->getService()->update($id, $data, null);
        if ($result->get('status')) {
            $model = $result->get('model');
            return [
                'status' => true,
                'data' => $model instanceof Model ? $model : null,
            ];
        }
        return [
            'status' => false,
            'message' => $result->get('message'),
        ];
    }

    public function __delete($id)
    {
        $result = $this->getService()->delete($id, null);
        if ($result->get('status')) {
            return [
                'status' => true,
            ];
        }
        return [
            'status' => false,
            'message' => $result->get('message'),
        ];
    }

    protected function getRequest()
    {
    }

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
