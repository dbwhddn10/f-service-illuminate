<?php

namespace Illuminate\Extend\Service\Query;

use Illuminate\Extend\Collection;
use Illuminate\Extend\Model;
use Illuminate\Extend\Service;

class SelectQueryService extends Service
{
    public static function getArrBindNames()
    {
        return [
            'available_expands'
                => 'options for {{expands}}',

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

            'result.expands' => ['expands', 'result', function ($expands, $result) {

                if ( $result instanceof Model )
                {
                    $expands = preg_split('/\s*,\s*/', $expands);
                    $collection = $result->newCollection();
                    $collection->push($result);
                }
                else if ( $result instanceof Collection )
                {
                    $collection = $result;
                }

                $collection->loadVisible($expands);
            }],
        ];
    }

    public static function getArrLoaders()
    {
        return [
            'available_expands' => ['model_class', function ($modelClass) {

                return inst($modelClass)->getExpandable();
            }],

            'available_fields' => ['model_class', function ($modelClass) {

                $model = inst($modelClass);

                return array_merge($model->getFillable(), $model->getGuarded());
            }],

            'model_class' => [function () {

                throw new \Exception;
            }],

            'query' => ['model_class', function ($modelClass) {

                return $modelClass::query();
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
            'expands'
                => ['string', 'several_in:{{available_expands}}'],

            'fields'
                => ['string', 'several_in:{{available_fields}}'],
        ];
    }

    public static function getArrTraits()
    {
        return [];
    }
}
