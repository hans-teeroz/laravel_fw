<?php

namespace App\Services;

use App\Models\Base as ModelBase;
use Evenement\EventEmitterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

abstract class BaseService
{
    use EventEmitterTrait;

    protected $container;

    protected $db;

    protected $model;

    public function __construct()
    {
        $this->boot();
    }

    public function __get(string $key)
    {
        return $this->container->get($key);
    }

    protected function getApiRequest(): Request
    {
        return App::make(Request::class);
    }

    public function __call($method, $params)
    {
        if ($this->model) {
            return call_user_func_array([$this->model, $method], $params);
        }

        return call_user_func_array([$this->db->getDatabaseManager()->connection(), $method], $params);
    }

    public function addScope($callback)
    {
        if (is_callable($callback)) {
            $model = $this->model;
            $model::addGlobalScope($callback);
        }

        return $this;
    }

    protected function listenChangeHistory()
    {
        $this->on('updating', function (ModelBase $model) {
            $model->handleInitializeHistory(null, $model->created_at->format('Y-m-d H:i:s'));
        });

        $this->on('updated', function (ModelBase $model) {
            $model->handleChangeHistory($this->getAuthed());
        });
    }

    protected function boot()
    {
    }
}
