<?php

namespace Illuminate\Extend\Http;

use Illuminate\Support\Arr;
use Illuminate\Extend\Service;
use Illuminate\Extend\Service\Database\Feature\ModelFeatureService;
use Illuminate\Extend\Service\Database\Feature\OrderByFeatureService;
use Illuminate\Extend\Service\Database\Feature\ExpandsFeatureService;
use Illuminate\Extend\Service\Database\Feature\FieldsFeatureService;
use Illuminate\Extend\Service\Database\Feature\LimitFeatureService;
use Illuminate\Extend\Service\Database\PaginationListService;

class ServiceParameterSettingMiddleware
{
    public function handle($request, $next)
    {
        $response = $next($request);
        $content  = $response->getOriginalContent();

        if ( !Service::isInitable($content) )
        {
            return $response;
        }

        $class    = $content[0];
        $data     = Arr::get($content, 1, []);
        $names    = Arr::get($content, 2, []);
        $traits   = $class::getAllTraits()->all();
        $loaders  = $class::getAllLoaders()->all();

        if ( $request->bearerToken() && ! $request->offsetExists('token') )
        {
            $data['token']  = $segs[1];
            $names['token'] = 'header[authorization]';
        }
        else if ( $request->offsetExists('token') )
        {
            $data['token']  = $request->offsetGet('token');
            $names['token'] = '[token]';
        }

        if ( in_array(ExpandsFeatureService::class, $traits) && $request->offsetExists('expands') )
        {
            $data['expands']  = Arr::get($request->all(), 'expands', '');
            $names['expands'] = '[expands]';
        }

        if ( in_array(FieldsFeatureService::class, $traits) && $request->offsetExists('fields') )
        {
            $data['fields']  = Arr::get($request->all(), 'fields', '');
            $names['fields'] = '[fields]';
        }

        if ( in_array(LimitFeatureService::class, $traits) && $request->offsetExists('limit') )
        {
            $data['limit']  = Arr::get($request->all(), 'limit', '');
            $names['limit'] = '[limit]';
        }

        if ( in_array(ModelFeatureService::class, $traits) && $request->route('id') )
        {
            $data['id']  = $request->route('id');
            $names['id'] = $request->route('id');
        }

        if ( in_array(OrderByFeatureService::class, $traits) && $request->offsetExists('order_by') )
        {
            $data['order_by']  = Arr::get($request->all(), 'order_by', '');
            $names['order_by'] = '[order_by]';
        }

        if ( in_array(PaginationListService::class, $traits) && $request->offsetExists('cursor_id') )
        {
            $data['cursor_id']  = Arr::get($request->all(), 'cursor_id', '');
            $names['cursor_id'] = '[cursor_id]';
        }

        if ( in_array(PaginationListService::class, $traits) && $request->offsetExists('page') )
        {
            $data['page']  = Arr::get($request->all(), 'page', '');
            $names['page'] = '[page]';
        }

        $response->{Arr::last(explode('\\', get_class($response))) == 'Response' ? 'setContent': 'setData'}([$class, $data, $names]);

        return $response;
    }
}
