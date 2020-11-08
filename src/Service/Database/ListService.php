<?php

namespace Illuminate\Extend\Service\Database;

use Illuminate\Extend\Service;
use Illuminate\Extend\Service\Database\Trait\ExpandsTraitService;
use Illuminate\Extend\Service\Database\Trait\FieldsTraitService;
use Illuminate\Extend\Service\Database\Trait\OrderByTraitService;

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
            ExpandsTraitService::class,
            FieldsTraitService::class,
            OrderByTraitService::class,
        ];
    }
}
