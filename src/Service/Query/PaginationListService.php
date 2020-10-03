<?php

namespace Illuminate\Extend\Service\Query;

use Illuminate\Extend\Service;
use Illuminate\Extend\Service\Query\Pagination\CursorPaginationService;
use Illuminate\Extend\Service\Query\Pagination\OffsetPaginationService;

class PaginationListService extends Service
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
            'cursor' => ['model_class', 'cursor_id', function ($modelClass, $cursorId) {

                throw new \Exception;
            }],

            'result' => ['cursor', 'limit', 'order_by_array', 'page', 'query', function ($cursor='', $limit, $orderByArray='', $page='', $query) {

                if ( $page !== '' )
                {
                    return [OffsetPaginationService::class, [
                        'limit'
                            => $limit,
                        'page'
                            => $page,
                        'query'
                            => $query,
                    ], [
                        'page'
                            => '{{page}}',
                    ]];
                }
                else
                {
                    return [CursorPaginationService::class, [
                        'cursor'
                            => $cursor,
                        'limit'
                            => $limit,
                        'order_by_array'
                            => $orderByArray,
                        'query'
                            => $query,
                    ]];
                }
            }]
        ];
    }

    public static function getArrPromiseLists()
    {
        return [];
    }

    public static function getArrRuleLists()
    {
        return [
            'cursor_id'
                => ['integer'],
        ];
    }

    public static function getArrTraits()
    {
        return [
            LimitQueryService::class,
            OrderQueryService::class,
            SelectQueryService::class,
        ];
    }
}
