<?php

namespace Illuminate\Extend;

use Illuminate\Support\Arr;

class Collection extends \Illuminate\Database\Eloquent\Collection {

    public function loadVisible($list)
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
                $model = app($modelClass);
                $columns = array_diff(array_merge($model->getFillable(), $model->getGuarded()), $model->getHidden());
                $groupList->load($rel.':'.implode(',', $columns));
            }

            if ( $v !== true )
            {
                $this->loadRel(new static($collect->pluck($rel)->all()), $v);
            }
        }
    }

    // public function loadVisible($relations)
    // {
    //     $relations = is_array($relations) ? $relations : func_get_args();

    //     foreach ( $relations as $relation )
    //     {
    //         $collect = $this;
    //         $rels  = explode('.', $relation);

    //         while (!empty($rels))
    //         {
    //             $models = [];

    //             foreach ( $collect as $item )
    //             {
    //                 if ( is_a($item, static::class) )
    //                 {
    //                     $models = array_merge($models, $item->all());
    //                 }
    //                 else
    //                 {
    //                     array_push($models, $item);
    //                 }
    //             }

    //             if ( empty($models) )
    //             {
    //                 break;
    //             }

    //             $rel     = array_shift($rels);
    //             $model   = $models[0]->{$rel}()->getModel();
    //             $columns = array_diff(array_merge($model->getFillable(), $model->getGuarded()), $model->getHidden());
    //             $collect = new static($models);
    //             $collect->load($rel.':'.implode(',', $columns));
    //             $collect = new static($collect->pluck($rel)->all());
    //         }
    //     }
    // }
}

