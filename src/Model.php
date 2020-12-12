<?php

namespace Dbwhddn10\FService\Illuminate;

use Closure;
use Dbwhddn10\FService\Illuminate\Collection;
use Dbwhddn10\FService\Illuminate\Query;
use Dbwhddn10\FService\Illuminate\Relation;

class Model extends \Illuminate\Database\Eloquent\Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    public $incrementing = false;
    protected $guarded = [];

    public function setCast($key, $value)
    {
        $this->casts[$key] = $value;
    }

    public function newEloquentBuilder($query)
    {
        return new Query($query);
    }

    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    public function newSubIdQuery()
    {
        return $this->setKeysForSaveQuery($this->newModelQuery());
    }

    public function relation($related, array $localKeys, array $otherKeys, $isManyRelation)
    {
        $query = (new $related)->newQuery();

        return new Relation($query, $this, $localKeys, $otherKeys, $isManyRelation);
    }
}
