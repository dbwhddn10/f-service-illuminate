<?php

namespace Illuminate\Extend\Service\Query;

use Illuminate\Extend\Service;

class RandomListService extends Service
{
    public static function getArrBindNames()
    {
        return [];
    }

    public static function getArrCallbackLists()
    {
        return [
            'query.order_by_rand' => ['query', function ($query, $limit) {

                $query->orderByRaw('RAND()');
            }],
        ];
    }

    public static function getArrLoaders()
    {
        return [];
    }

    public static function getArrPromiseLists()
    {
        return [
            'query.order_by_rand'
                => ['query.order_by_array'],
        ];
    }

    public static function getArrRuleLists()
    {
        return [];
    }

    public static function getArrTraits()
    {
        return [
            LimitQueryService::class,
            ListService::class,
        ];
    }
}
