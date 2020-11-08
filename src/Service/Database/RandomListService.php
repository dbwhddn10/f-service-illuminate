<?php

namespace Illuminate\Extend\Service\Database;

use Illuminate\Extend\Service;
use Illuminate\Extend\Service\Database\Trait\ExpandsTraitService;
use Illuminate\Extend\Service\Database\Trait\FieldsTraitService;
use Illuminate\Extend\Service\Database\Trait\LimitTraitService;

class RandomListService extends Service
{
    public static function getArrBindNames()
    {
        return [];
    }

    public static function getArrCallbackLists()
    {
        return [
            'query.order_by' => ['query', function ($query) {

                $query->orderByRaw('RAND()');
            }],
        ];
    }

    public static function getArrLoaders()
    {
        return [
            'select_query' => ['query', function ($query) {

                return [SelectQueryService::class, [
                    'query'
                        => $query
                ]];
            }],

            'result' => ['select_query', function ($selectQuery) {

                return $selectQuery->get();
            }],
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
            ExpandsTraitService::class,
            FieldsTraitService::class,
            LimitTraitService::class,
        ];
    }
}
