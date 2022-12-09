<?php

namespace App\Lib\SupportTrait;

use App\Jobs\SyncDataJob;
use App\Lib\Helper\Result;
use App\Models\Base as BaseModel;
use App\Services\ApiService;
use ArrayAccess;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

trait ApiTrait
{
    protected $maxPerPage = 1000;

    protected $relations = [];

    protected $fieldsName = null;

    protected $filterData = [];

    protected function newQuery()
    {
        return $this->query();
    }

    protected function fetchQuery(Request $request)
    {
        return $this->newQuery();
    }

    protected function findQuery(Request $request)
    {
        return $this->fetchQuery($request);
    }

    protected function listQuery(Request $request)
    {
        return $this->fetchQuery($request);
    }

    protected function updateQuery()
    {
        return $this->newQuery();
    }

    protected function deleteQuery()
    {
        return $this->newQuery();
    }

    public function getMany(Request $request)
    {
        if (boolval($request->query->get('_noPagination', 0))) {
            return $this->getWithoutPage($request);
        } else {
            return $this->getWithPage($request);
        }
    }

    protected function getWithPage(Request $request)
    {
        $page = max(intval($request->query->get('_page', 1)), 1);
        $perPage = min(max(intval($request->query->get('_perPage', 10)), 1), $this->maxPerPage);
        $query = $this->builderForGetMany($request);
        $items = $this->beforeRenderGetMany($query->paginate($perPage, ['*'], '_page', $page), $request);
        return $this->renderApi($items, $request);
    }

    protected function getWithoutPage(Request $request)
    {
        $query = $this->builderForGetMany($request);
        $items = $this->beforeRenderGetMany($query->get(), $request);
        return $this->renderApi($items, $request);
    }

    protected function getCastValue($key, $value)
    {
        $casts = c($this->model)->getCasts();
        if ($cast = $casts[$key] ?? null) {
            switch ($cast) {
                case 'int':
                case 'integer':
                    return strval(intval($value));
                case 'float':
                    return strval(floatval($value));
                case 'bool':
                case 'boolean':
                    if (!$value) {
                        return false;
                    };
                    if ($value == 'true' || $value == '1' || $value == 1) {
                        return true;
                    }
                    if ($value == 'false' || $value == '0' || $value == 0) {
                        return false;
                    }
                    // no break
                case 'json':
                case 'array':
                    return json_encode($value);
                default:
                    return $value;
            }
        }
        return $value;
    }

    protected function builderForGetMany(Request $request)
    {
        $query = $this->listQuery($request);

        $defaultOrderBy = $this->defaultOrderBy();
        $defaultOrderByName = $defaultOrderBy[0] ?? null;
        $defaultOrderByType = $defaultOrderBy[1] ?? 'asc';
        $hasDefaultOrderByName = false;
        $orderBy = [];
        $orderByArr = array_filter(explode(';', $request->query->get('_orderBy', '')));
        foreach ($orderByArr as $orderByStr) {
            $arr = explode(':', $orderByStr);
            if ($this->isOrderbyable($name = $arr[0] ?? null)) {
                if (in_array($type = $arr[1] ?? 'asc', ['asc', 'desc'])) {
                    $orderBy[] = [$name, $type];
                    if ($name === $defaultOrderByName) {
                        $hasDefaultOrderByName = true;
                    }
                }
            }
        }
        if (!$hasDefaultOrderByName && in_array($defaultOrderByType, ['asc', 'desc'])) {
            $orderBy[] = [$defaultOrderByName, $defaultOrderByType];
        }
        foreach ($orderBy as $orderByItem) {
            $query->orderBy(...$orderByItem);
        }
        $this->filterData = $this->getFilters($request);
        $filters = [];
        foreach ($this->filterData as $column => $value) {
            if ($this->isFilterable($column) && $value != '') {
                $value = $this->getCastValue($column, $value);
                $filters[] = $this->filter($column, $value, $this->filterData);
            }
        }
        foreach ($filters as $filter) {
            if ($filter) {
                if (is_array($filter)) {
                    $query->where([$filter]);
                } elseif (is_callable($filter)) {
                    call_user_func($filter, $query);
                }
            }
        }
        $this->loadRelations($request, $query);
        return $query;
    }

    protected function getRelations(Request $request): array
    {
        return array_filter(explode(',', strval($request->query->get('_relations', ''))));
    }

    protected function loadRelations(Request $request, Builder $query)
    {
        $relations = $this->getRelations($request);
        $model = $this->make();
        $map = $this->mapWiths();
        foreach ($relations as $relation) {
            if (array_key_exists($relation, $map) && is_callable($map[$relation])) {
                call_user_func($map[$relation], $query);
            } else {
                if (method_exists($model, $relation)) {
                    $result = call_user_func([$model, $relation]);
                    if ($result instanceof Relation) {
                        $query->with($relation);
                    }
                }
            }
        }
    }

    protected function mapWiths(): array
    {
        return [];
    }

    abstract protected function getOrderbyableFields(): array;

    protected function isOrderbyable($field): bool
    {
        return in_array($field, $this->getOrderbyableFields());
    }

    abstract protected function getFilterableFields(): array;

    protected function isFilterable($field)
    {
        return is_string($field) && in_array($field, $this->getFilterableFields());
    }

    protected function getFilters(Request $request)
    {
        $raw = $request->query->get('_filter', '');
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

    protected function getFilterData()
    {
        return $this->filterData;
    }

    protected function mapFilters(): array
    {
        return [];
    }

    protected function defaultOrderBy(): array
    {
        return ['id', 'desc'];
    }

    protected function filter($field, $value, $filters)
    {
        $maps = $this->mapFilters();
        if (isset($maps[$field]) && is_callable($maps[$field])) {
            return call_user_func($maps[$field], $value, $filters);
        }
        return [$field, '=', $value];
    }

    public function getOne(Request $request)
    {
        $query = $this->findQuery($request);
        $id = $this->getRequestId($request);
        $item = $id ? $query->find($id) : null;
        if ($item instanceof BaseModel) {
            $item = $this->beforeRenderGetOne($item, $request);
            return $this->renderApi($item, $request);
        }
    }

    protected function getRequestId(Request $request)
    {
        return $request->route('id');
    }

    protected function beforeRenderGetOne(BaseModel $item, Request $request)
    {
        return $item;
    }

    protected function beforeRenderGetMany($items, Request $request)
    {
        return $items;
    }

    abstract protected function fields(): array;

    private function getFields(Request $request): array
    {
        if (is_string($this->fieldsName)) {
            $allow_fields = $this->fields();
            return array_intersect($allow_fields, array_filter(explode(',', $request->query->get($this->fieldsName))));
        }
        return [];
    }

    protected function renderApi($data, Request $request)
    {
        $relations = $this->getRelations($request);
        $relationCallbacks = [];
        foreach ($relations as $relation) {
            $name = "include" . ucfirst($relation);
            if (method_exists($this, $name)) {
                $arr = call_user_func([$this, $name]);
                if (count($arr) === 3 && $arr[0] instanceof ApiService && in_array($arr[1], ['item', 'items']) && is_callable($arr[2])) {
                    $relationCallbacks[$relation] = [
                        'fields' => $arr[0]->getFields($request),
                        'callback' => [$arr[0], $arr[1]],
                        'data_callback' => $arr[2],
                    ];
                }
            }
        }
        $fields = $this->getFields($request);

        $result = null;
        $meta = [
            'relations' => $this->relations,
            'fields' => $this->fields(),
        ];
        if ($data instanceof BaseModel) {
            $result = $this->item($data, $fields);
            foreach ($relationCallbacks as $name => $relationCallback) {
                $relationData = call_user_func($relationCallback['data_callback'], $data);
                $result[$name] = call_user_func($relationCallback['callback'], $relationData, $relationCallback['fields']);
            }
        } elseif ($data instanceof LengthAwarePaginator) {
            $result = $this->items($data->getCollection(), $fields, $relationCallbacks);
            $meta['pagination'] = [
                'page' => $data->currentPage(),
                'perPage' => $data->perPage(),
                'total' => $data->total(),
                'lastPage' => $data->lastPage(),
            ];
        } elseif ($data instanceof ArrayAccess) {
            $result = $this->items($data, $fields, $relationCallbacks);
        }
        return [
            'status' => true,
            'data' => $result,
            'meta' => $meta,
        ];
    }

    private function items(ArrayAccess $items, array $fields, array $relationCallbacks = [])
    {
        $rows = [];
        foreach ($items as $item) {
            $row = $this->item($item, $fields);
            foreach ($relationCallbacks as $name => $relationCallback) {
                $relationData = call_user_func($relationCallback['data_callback'], $item);
                $row[$name] = call_user_func($relationCallback['callback'], $relationData, $relationCallback['fields']);
            }
            $rows[] = $row;
        }
        return $rows;
    }

    private function item($item, array $fields)
    {
        if (!$item instanceof Model) {
            return null;
        }
        $row = ['id' => $item->id];
        foreach ($fields as $field) {
            $name = "get_{$field}_value";
            $value = $item->{$field};
            if (method_exists($this, $name)) {
                $value = call_user_func([$this, $name], $value, $item);
            }
            $row[$field] = $value;
        }
        if ($item->timestamps) {
            if ($item->created_at instanceof DateTime) {
                $row['created_at'] = $item->created_at->format('Y-m-d H:i:s');
            }
            if ($item->updated_at instanceof DateTime) {
                $row['updated_at'] = $item->updated_at->format('Y-m-d H:i:s');
            }
        }
        return $row;
    }

    protected function errorResult($e)
    {
        $message = method_exists($e, 'getMessage') ? $e->getMessage() : "";
        // return new Result([
        //     'status' => false,
        //     'message' => $message,
        // ]);

        $response = new Response([
            'status' => false,
            'message' => $message,
        ], 200);
        throw new ValidationException($message, $response);
    }

    public function create($data, $callback = null)
    {
        DB::beginTransaction();
        try {
            $model = $this->make($data);
            if (is_callable($callback)) {
                call_user_func($callback, $model);
            }
            $this->emit('creating', [$model]);
            $this->emit('saving', [$model]);
            $save = $model->save();
            $this->emit('created', [$model]);
            $this->emit('saved', [$model]);

            if (env('REDIS_ENABLE', false)) {
                if ($save) {
                    $pathInfo = \request()->getPathInfo();
                    SyncDataJob::dispatch($pathInfo);
                }
            }
            DB::commit();
            return new Result([
                'status' => $save,
                'model' => $model,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->errorResult($e);
        }
    }

    public function update($id, array $data, $callback = null)
    {
        DB::beginTransaction();
        try {
            $query = $this->updateQuery();
            $model = $query->find($id);
            if (!$model instanceof BaseModel) {
                throw new Exception("Record not found!");
            }

            $model->fill($data);
            if (is_callable($callback)) {
                call_user_func($callback, $model);
            }
            $this->emit('updating', [$model]);
            $this->emit('saving', [$model]);
            $save = $model->save();
            $this->emit('updated', [$model]);
            $this->emit('saved', [$model]);

            if (env('REDIS_ENABLE', false)) {
                if ($save) {
                    $pathInfo = str_replace("/$id", '', \request()->getPathInfo());
                    SyncDataJob::dispatch($pathInfo);
                }
            }

            DB::commit();
            return new Result([
                'status' => $save,
                'model' => $model,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->errorResult($e);
        }
    }

    public function updateModel(BaseModel $model, array $data, $callback = null)
    {
        DB::beginTransaction();
        try {
            $model->fill($data);
            if (is_callable($callback)) {
                call_user_func($callback, $model);
            }
            $this->emit('updating', [$model]);
            $this->emit('saving', [$model]);
            $save = $model->save();
            $this->emit('updated', [$model]);
            $this->emit('saved', [$model]);

            DB::commit();
            return new Result([
                'status' => $save,
                'model' => $model,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->errorResult($e);
        }
    }

    public function delete($id, $callback = null)
    {
        DB::beginTransaction();
        try {
            $query = $this->deleteQuery();
            $model = $query->find($id);
            if (!$model instanceof BaseModel) {
                throw new Exception("Record not found!");
            }
            if (is_callable($callback)) {
                call_user_func($callback, $model);
            }

            $this->emit('deleting', [$model]);
            $save = $model->delete();
            $this->emit('deleted', [$model]);

            if (env('REDIS_ENABLE', false)) {
                if ($save) {
                    $pathInfo = str_replace("/$id", '', \request()->getPathInfo());
                    SyncDataJob::dispatch($pathInfo, get_class($model), $model->getEndcodeAllRelations());
                }
            }

            DB::commit();
            return new Result([
                'status' => $save,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->errorResult($e);
        }
    }
}
