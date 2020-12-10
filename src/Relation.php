<?php

namespace Dbwhddn10\Illuminate;

use Closure;
use Dbwhddn10\Illuminate\Model;
use Dbwhddn10\Illuminate\Query;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Relation extends \Illuminate\Database\Eloquent\Relations\Relation
{
    protected $localKeys;
    protected $otherKeys;
    protected $isManyRelation;

    public function __construct(Query $query, Model $parent, array $localKeys, array $otherKeys, $isManyRelation)
    {
        $this->localKeys      = $localKeys;
        $this->otherKeys      = $otherKeys;
        $this->isManyRelation = $isManyRelation;

        parent::__construct($query, $parent);
    }

    public function getLocalValue($model, $key)
    {
        if ( $key == ':auth_user_id:' && Auth::user() )
        {
            $val = Auth::user()->getKey();
        }
        else if ( $key == ':auth_user_id:')
        {
            $val = null;
        }
        else if ( $key == ':model_type:' )
        {
            $val = array_flip(static::morphMap())[get_class($model)];
        }
        else
        {
            $val = $model->getAttributes()[$key];
        }

        return $val;
    }

    public function addConstraints()
    {
        if (static::$constraints) {

            foreach ( $this->localKeys as $i => $key )
            {
                $otherKey = $this->otherKeys[$i];

                $this->query->where($otherKey, $this->getLocalValue($this->parent, $key));
            }
        }
    }

    public function addEagerConstraints(array $models)
    {
        $colRow  = 'ROW(`'.implode('`,`', $this->otherKeys).'`)';
        $valRows = [];

        foreach ( $models as $model )
        {
            $vals = [];

            foreach ( $this->localKeys as $key )
            {
                $vals[] = $this->getLocalValue($model, $key);
            }

            $valRows[] = 'ROW(\''.implode('\',\'', $vals).'\')';
        }

        $this->query->whereRaw($colRow.' in ('.implode(',', $valRows).')');
    }

    public function initRelation(array $models, $relation)
    {
        foreach ( $models as $model )
        {
            if ( $this->isManyRelation )
            {
                $model->setRelation($relation, $this->query->getModel()->newCollection());
            }
            else
            {
                $model->setRelation($relation, null);
            }
        }

        return $models;
    }

    public function match(array $models, Collection $results, $relation)
    {
        $list = [];

        foreach ( $results as $result )
        {
            $keyVals = [];

            foreach ( $this->otherKeys as $key )
            {
                $keyVals[] = $result->getAttributes()[$key];
            }

            $key = implode(',', $keyVals);

            $list[$key] = $result;
        }

        foreach ( $models as $model )
        {
            $keyVals = [];

            foreach ( $this->localKeys as $key )
            {
                $keyVals[] = $this->getLocalValue($model, $key);
            }

            $key = implode(',', $keyVals);

            if ( array_key_exists($key, $list) )
            {
                if ( $model->getRelation($relation) instanceof Collection )
                {
                    $model->getRelation($relation)->push($list[$key]);
                }
                else
                {
                    $model->setRelation($relation, $list[$key]);
                }
            }
        }

        return $models;
    }

    public function getResults()
    {
        foreach ( $this->localKeys as $key )
        {
            if ( is_null($this->parent->{$key}) )
            {
                return;
            }
        }

        if ( $this->isManyRelation )
        {
            return $this->get();
        }
        else
        {
            return $this->first();
        }
    }
}
