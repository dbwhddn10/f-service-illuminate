<?php

namespace Illuminate\Extend\Service\Database;

use Illuminate\Extend\Service;
use Illuminate\Extend\Service\Database\Feature\ExpandsFeatureService;
use Illuminate\Extend\Service\Database\Feature\FieldsFeatureService;
use Illuminate\Extend\Service\Database\Feature\LimitFeatureService;

class RandomListService extends Service
{
    public static function getArrBindNames()
    {
        return [];
    }

    public static function getArrCallbackLists()
    {
        return [
            'query.order_by' => function ($query) {

                $query->orderByRaw('RAND()');
            },
        ];
    }

    public static function getArrLoaders()
    {
        return [
            'result' => function ($selectQuery) {

                return $selectQuery->get();
            },

            'select_query' => function ($query) {

                return [SelectQueryService::class, [
                    'query'
                        => $query
                ]];
            },
        ];
    }

    public static function getArrPromiseLists()
    {
        return [];
    }

    public static function getArrRuleLists()
    {
        return [];
    }

    public static function getArrTraits()
    {
        return [
            ExpandsFeatureService::class,
            FieldsFeatureService::class,
            LimitFeatureService::class,
        ];
    }
}
