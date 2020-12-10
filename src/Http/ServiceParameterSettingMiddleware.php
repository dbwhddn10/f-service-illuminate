<?php

namespace Dbwhddn10\Illuminate\Http;

use Illuminate\Support\Arr;
use Dbwhddn10\Illuminate\Service;
use Dbwhddn10\Illuminate\Service\Database\Feature\ModelFeatureService;
use Dbwhddn10\Illuminate\Service\Database\Feature\OrderByFeatureService;
use Dbwhddn10\Illuminate\Service\Database\Feature\ExpandsFeatureService;
use Dbwhddn10\Illuminate\Service\Database\Feature\FieldsFeatureService;
use Dbwhddn10\Illuminate\Service\Database\Feature\LimitFeatureService;

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

        if ( $request->offsetExists('token') )
        {
            $data['token']  = $request->offsetGet('token');
            $names['token'] = '[token]';
        }
        else
        {
            $data['token']  = $request->bearerToken() ? : '';
            $names['token'] = 'header[authorization]';
        }

        if ( in_array(ExpandsFeatureService::class, $traits) )
        {
            $data['expands'] = Arr::get($request->all(), 'expands', '');
            $names['expands'] = '[expands]';
        }

        if ( in_array(FieldsFeatureService::class, $traits) )
        {
            $data['fields'] = Arr::get($request->all(), 'fields', '');
            $names['fields'] = '[fields]';
        }

        if ( in_array(LimitFeatureService::class, $traits) )
        {
            $data['limit'] = Arr::get($request->all(), 'limit', '');
            $names['limit'] = '[limit]';
        }

        if ( in_array(ModelFeatureService::class, $traits) )
        {
            $data['id']  = $request->route('id') ? $request->route('id') : '';
            $names['id'] = $request->route('id') ? $request->route('id') : '';
        }

        if ( in_array(OrderByFeatureService::class, $traits) )
        {
            $data['order_by'] = Arr::get($request->all(), 'order_by', '');
            $names['order_by'] = '[order_by]';
        }

        if ( array_key_exists('cursor', $loaders) )
        {
            $data['cursor_id']  = Arr::get($request->all(), 'cursor_id', '');
            $data['page']       = Arr::get($request->all(), 'page', '');
            $names['cursor_id'] = '[cursor_id]';
            $names['page']      = '[page]';
        }

        $response->{Arr::last(explode('\\', get_class($response))) == 'Response' ? 'setContent': 'setData'}([$class, $data, $names]);

        return $response;
    }
}
