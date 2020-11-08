<?php

namespace Illuminate\Extend\Service\Database\Trait;

use Illuminate\Extend\Service;
use Illuminate\Extend\Service\Database\Trait\QueryTraitService;

class FieldsTraitService extends Service
{
    public static function getArrBindNames()
    {
        return [
            'available_fields'
                => 'options for {{fields}}',
        ];
    }

    public static function getArrCallbackLists()
    {
        return [
            'query.fields' => ['available_fields', 'fields', 'query', function ($availableFields, $fields='', $query) {

                $fields = $fields ? preg_split('/\s*,\s*/', $fields) : $availableFields;

                $query->select($fields);
            }],
        ];
    }

    public static function getArrLoaders()
    {
        return [
            'available_fields' => ['model_class', function ($modelClass) {

                $model = new $modelClass;

                return array_merge($model->getFillable(), $model->getGuarded());
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
            'fields'
                => ['string', 'several_in:{{available_fields}}'],
        ];
    }

    public static function getArrTraits()
    {
        return [
            QueryTraitService::class,
        ];
    }
}
