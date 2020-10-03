<?php

namespace Illuminate\Extend\Service\Query;

use Illuminate\Extend\Service;

class FindService extends Service
{
    public static function getArrBindNames()
    {
        return [];
    }

    public static function getArrCallbackLists()
    {
        return [
            'query.id' => ['id', 'query', function ($id, $query) {

                $query->where($query->getModel()->getKeyName(), $id);
            }],
        ];
    }

    public static function getArrLoaders()
    {
        return [
            'model' => ['result', function ($result) {

                return $result;
            }],

            'result' => ['query', function ($query) {

                return $query->get()->first();
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
            SelectQueryService::class,
        ];
    }
}
