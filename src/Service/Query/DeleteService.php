<?php

namespace Illuminate\Extend\Service\Query;

use Illuminate\Extend\Service;

class DeleteService extends Service
{
    public static function getArrBindNames()
    {
        return [];
    }

    public static function getArrCallbackLists()
    {
        return [
            'result.model' => ['model', function ($model) {

                $model->delete();
            }],
        ];
    }

    public static function getArrLoaders()
    {
        return [
            'result' => [function () {

                return null;
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
            FindService::class,
        ];
    }
}
