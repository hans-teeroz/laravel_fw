<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

// use App\lib\Builder\NewModel as Model;
//use Jenssegers\Mongodb\Eloquent\Model;

abstract class Base extends Model
{
    use HasFactory;

//    use \Bkwld\Cloner\Cloneable;

    protected $raw = [];

    public function fill(array $attributes)
    {
        $this->raw = $attributes;
        return parent::fill($attributes);
    }

    public function getRaw(string $name, $default = null)
    {
        if (isset($this->raw[$name])) {
            return $this->raw[$name];
        }
        return $default;
    }

    public function freshTimestamp()
    {
        return Date::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));
    }

    public function getAllRelations(Model $model = null, $heritage = 'all')
    {
        $model = $model ?: $this;
        $modelName = get_class($model);
        $types = ['children' => 'Has', 'parents' => 'Belongs', 'all' => ''];
        $heritage = in_array($heritage, array_keys($types)) ? $heritage : 'all';
        //        if (\Illuminate\Support\Facades\Cache::has($modelName."_{$heritage}_relations")) {
        //            return \Illuminate\Support\Facades\Cache::get($modelName."_{$heritage}_relations");
        //        }
        $reflectionClass = new \ReflectionClass($model);
        $traits = $reflectionClass->getTraits();    // Use this to omit trait methods
        $traitMethodNames = [];
        foreach ($traits as $name => $trait) {
            $traitMethods = $trait->getMethods();
            foreach ($traitMethods as $traitMethod) {
                $traitMethodNames[] = $traitMethod->getName();
            }
        }

        // Checking the return value actually requires executing the method.  So use this to avoid infinite recursion.
        $currentMethod = collect(explode('::', __METHOD__))->last();
        $filter = $types[$heritage];
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);  // The method must be public
        $methods = collect($methods)->filter(function ($method) use ($modelName, $traitMethodNames, $currentMethod) {
            $methodName = $method->getName();
            if (
                !in_array($methodName, $traitMethodNames)   //The method must not originate in a trait
                && strpos($methodName, '__') !== 0  //It must not be a magic method
                && $method->class === $modelName    //It must be in the self scope and not inherited
                && !$method->isStatic() //It must be in the this scope and not static
                && $methodName != $currentMethod    //It must not be an override of this one
            ) {
                $parameters = (new \ReflectionMethod($modelName, $methodName))->getParameters();
                return collect($parameters)->filter(function ($parameter) {
                    return !$parameter->isOptional();   // The method must have no required parameters
                })->isEmpty();  // If required parameters exist, this will be false and omit this method
            }
            return false;
        })->mapWithKeys(function ($method) use ($model, $filter) {
            $methodName = $method->getName();
            $relation = $model->$methodName();  //Must return a Relation child. This is why we only want to do this once
            if (is_subclass_of($relation, Relation::class)) {
                $type = (new \ReflectionClass($relation))->getShortName();  //If relation is of the desired heritage
                if (!$filter || strpos($type, $filter) === 0) {
                    return [$methodName => get_class($relation->getRelated())]; // ['relationName'=>'relatedModelClass']
                }
            } else {
                return [$methodName => ''];
            }
            return false;   // Remove elements reflecting methods that do not have the desired return type
        })->toArray();

        Cache::forever($modelName . "_{$heritage}_relations", $methods);
        return $methods;
    }

    public function getEndcodeAllRelations()
    {
        $arrRelations = $this->getAllRelations();
        if (is_array($arrRelations)) {
            return array_map(function ($item) {
                return base64_encode($item);
            }, $arrRelations);
        }
        return [];
    }
}
