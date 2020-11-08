<?php

namespace Illuminate\Extend\Service\Database;

use Illuminate\Extend\Service;
use Illuminate\Extend\Service\Database\Trait\ExpandsTraitService;
use Illuminate\Extend\Service\Database\Trait\FieldsTraitService;
use Illuminate\Extend\Service\Database\Trait\ModelTraitService;

class FindService extends Service
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
            'result' => ['model', function ($model) {

                return $model;
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
            'id'
                => ['required', 'integer'],

            'model'
                => ['not_null']
        ];
    }

    public static function getArrTraits()
    {
        return [
            ExpandsTraitService::class,
            FieldsTraitService::class,
            ModelTraitService::class,
        ];
    }
}
