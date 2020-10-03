<?php

namespace Illuminate\Extend\Service\Query;

use Illuminate\Extend\Service;

class OrderQueryService extends Service
{
    public static function getArrBindNames()
    {
        return [
            'available_group_by'
                => 'options for {{group_by}}',

            'available_order_by'
                => 'options for {{order_by}}',
        ];
    }

    public static function getArrCallbackLists()
    {
        return [
            'query.group_by' => ['group_by', 'query', function ($groupBy, $query) {

                $groupBy = preg_split('/\s*,\s*/', $groupBy);

                $query->groupBy($groupBy);
            }],

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
            'available_group_by' => [function () {

                return [];
            }],

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
            'group_by'
                => ['string', 'in_array:{{available_group_by}}.*'],

            'order_by'
                => ['string', 'in_array:{{available_order_by}}.*'],
        ];
    }

    public static function getArrTraits()
    {
        return [
            SelectQueryService::class
        ];
    }
}
