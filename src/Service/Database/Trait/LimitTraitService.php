<?php

namespace Illuminate\Extend\Service\Database\Trait;

use Illuminate\Extend\Service;
use Illuminate\Extend\Service\Database\Trait\QueryTraitService;

class LimitTraitService extends Service
{
    public static function getArrBindNames()
    {
        return [];
    }

    public static function getArrCallbackLists()
    {
        return [
            'query.limit' => ['limit', 'query', function ($limit, $query) {

                $query->take($limit);
            }],
        ];
    }

    public static function getArrLoaders()
    {
        return [
            'limit' => [function () {

                return 30;
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
            'limit'
                => ['required', 'integer', 'max:120', 'min:1'],
        ];
    }

    public static function getArrTraits()
    {
        return [
            QueryTraitService::class,
        ];
    }
}
