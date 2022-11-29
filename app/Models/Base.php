<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
