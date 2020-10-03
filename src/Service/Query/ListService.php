<?php

namespace Illuminate\Extend\Service\Query;

use Illuminate\Extend\Service;

class ListService extends Service
{
    public static function getArrBindNames()
    {
        return [];
    }

    public static function getArrCallbackLists()
    {
        return [];
    }

    public static function getArrLoaders()
    {
        return [
            'result' => ['query', function ($query) {

                return $query->get();
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
            OrderQueryService::class,
            SelectQueryService::class,
        ];
    }
}
