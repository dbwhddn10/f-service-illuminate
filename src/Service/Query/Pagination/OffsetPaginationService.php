<?php

namespace Illuminate\Extend\Service\Query\Pagination;

use Illuminate\Extend\Service;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class OffsetPaginationService extends Service {

    public static function getArrBindNames()
    {
        return [];
    }

    public static function getArrCallbackLists()
    {
        return [
            'query.skip' => ['query', 'skip', function ($query, $skip) {

                $query->skip($skip);
            }],
        ];
    }

    public static function getArrLoaders()
    {
        return [
            'skip' => ['limit', 'page', function ($limit, $page) {

                return ( $page - 1 ) * $limit;
            }],

            'result' => ['limit', 'page', 'query', 'select_query', function ($limit, $page, $query, $selectQuery) {

                $query = (clone $query)->toBase();
                $query->limit = null;
                $query->offset = null;

                return app()->makeWith(LengthAwarePaginator::class, [
                    'items' => $selectQuery->get(),
                    'total' => $query->count(),
                    'perPage' => $limit,
                    'currentPage' => $page,
                    'options' => [
                        'path' => Paginator::resolveCurrentPath(),
                        'pageName' => 'page',
                    ]
                ]);
            }],

            'select_query' => ['query', function ($query) {

                $columns     = $query->getQuery()->columns;
                $model       = $query->getModel();
                $selectQuery = $model->query();
                $query       = (clone $query)->select($model->getKeyName());
                $ids         = $query->get()->modelKeys();

                $selectQuery->getQuery()->select($columns);
                $selectQuery->whereIn($model->getKeyName(), $ids);

                if ( ! empty($ids) )
                {
                    if ( $model->getKeyType() == 'string' )
                    {
                        foreach ( $ids as $i => $id )
                        {
                            $ids[$i] = '\''.$id.'\'';
                        }
                    }

                    $selectQuery->orderByRaw('FIELD('.$model->getKeyName().','.implode(',', $ids).')');
                }

                return $selectQuery;
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
            'page'
                => ['required', 'integer', 'min:1'],
        ];
    }

    public static function getArrTraits()
    {
        return [];
    }

}
