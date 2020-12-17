<?php

namespace Dbwhddn10\FService\Illuminate;

use Illuminate\Support\Arr;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
    public function load($list)
    {
        $list = is_array($list) ? $list : explode(',', $list);
        $relations = [];

        foreach ( $list as $key )
        {
            Arr::set($relations, $key, true);
        }

        $this->loadRel($this, $relations);
    }

    private function loadRel($collect, $relations)
    {
        if ( $collect->isEmpty() )
        {
            return;
        }

        $collect = $collect->filter(function ($item)
        {
            return $item != null;
        });

        if ( is_a($collect->first(), static::class) )
        {
            $collect = new static($collect->flatten(1)->all());
        }

        foreach ( $relations as $rel => $v )
        {
            $groupLists = $collect->groupBy(function ($item) use ($rel)
            {
                return get_class($item->{$rel}()->getModel());
            });

            foreach ( $groupLists as $modelClass => $groupList )
            {
                $model   = app($modelClass);
                $columns = array_diff(array_merge($model->getFillable(), $model->getGuarded()), $model->getHidden());
                $args    = [$rel.':'.implode(',', $columns)];

                call_user_func_array([$groupList, 'parent::load'], $args);
            }

            if ( $v !== true )
            {
                $this->loadRel(new static($collect->pluck($rel)->all()), $v);
            }
        }
    }
}
