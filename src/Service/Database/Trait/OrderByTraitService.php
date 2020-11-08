<?php

namespace Illuminate\Extend\Service\Database\Trait;

use Illuminate\Extend\Service;
use Illuminate\Extend\Service\Database\Trait\QueryTraitService;

class OrderByTraitService extends Service
{
    public static function getArrBindNames()
    {
        return [
            'available_order_by'
                => 'options for {{order_by}}',
        ];
    }

    public static function getArrCallbackLists()
    {
        return [
            'query.order_by_array' => ['order_by_array', 'query', function ($orderByArray, $query) {

                foreach ( $orderByArray as $key => $direction )
                {
                    $query->orderBy($key, $direction);
                }
            }],
        ];
    }

    public static function getArrLoaders()
    {
        return [
            'available_order_by' => ['model_class', function ($modelClass) {

                if ( $modelClass::CREATED_AT == null )
                {
                    return [(new $modelClass)->getKeyName().' desc', (new $modelClass)->getKeyName().' asc'];
                }
                else
                {
                    return [$modelClass::CREATED_AT.' desc', $modelClass::CREATED_AT.' asc'];
                }
            }],

            'order_by_array' => ['model_class', 'order_by', function ($modelClass, $orderBy) {

                $model   = new $modelClass;
                $orderBy = preg_replace('/\s+/', ' ', $orderBy);
                $orderBy = preg_replace('/\s*,\s*/', ',', $orderBy);
                $orders  = explode(',', $orderBy);
                $array   = [];

                foreach ( $orders as $order )
                {
                    $key       = explode(' ', $order)[0];
                    $direction = str_replace($key, '', $order);

                    $array[$key] = ltrim($direction);
                }

                if ( array_keys($array)[count($array)-1] != $model->getKeyName() )
                {
                    $array[$model->getKeyName()] = end($array);
                }

                return $array;
            }],
        ];
    }

    public static function getArrPromiseLists()
    {
        return [];
    }

    public static function getArrRuleLists()
    {
        return [
            'order_by'
                => ['string', 'in_array:{{available_order_by}}.*'],
        ];
    }

    public static function getArrTraits()
    {
        return [
            QueryTraitService::class,
        ];
    }
}
