<?php

namespace Dbwhddn10\FService\Illuminate\Http;

use Illuminate\Support\Arr;
use Dbwhddn10\FService\Service;

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

        $class     = $content[0];
        $data      = Arr::get($content, 1, []);
        $names     = Arr::get($content, 2, []);
        $ruleLists = $class::getAllRuleLists()->all();

        if ( !isset($data['token']) && $request->offsetExists('token') )
        {
            $data['token']  = $request->offsetGet('token');
            $names['token'] = '[token]';
        }
        else if ( !isset($data['token']) )
        {
            $data['token']  = $request->bearerToken() ? : '';
            $names['token'] = 'header[authorization]';
        }

        if ( array_key_exists('expands', $ruleLists) || $request->offsetExists('expands') )
        {
            $data['expands'] = Arr::get($request->all(), 'expands', '');
            $names['expands'] = '[expands]';
        }

        if ( array_key_exists('fields', $ruleLists) || $request->offsetExists('fields') )
        {
            $data['fields'] = Arr::get($request->all(), 'fields', '');
            $names['fields'] = '[fields]';
        }

        if ( array_key_exists('limit', $ruleLists) || $request->offsetExists('limit') )
        {
            $data['limit'] = Arr::get($request->all(), 'limit', '');
            $names['limit'] = '[limit]';
        }

        if ( $request->route('id') )
        {
            $data['id']  = $request->route('id') ? $request->route('id') : '';
            $names['id'] = $request->route('id') ? $request->route('id') : '';
        }

        if ( array_key_exists('order_by', $ruleLists) || $request->offsetExists('order_by') )
        {
            $data['order_by'] = Arr::get($request->all(), 'order_by', '');
            $names['order_by'] = '[order_by]';
        }

        if ( array_key_exists('cursor_id', $ruleLists) || $request->offsetExists('cursor_id') )
        {
            $data['cursor_id']  = Arr::get($request->all(), 'cursor_id', '');
            $names['cursor_id'] = '[cursor_id]';
        }

        if ( array_key_exists('page', $ruleLists) || $request->offsetExists('page') )
        {
            $data['page']  = Arr::get($request->all(), 'page', '');
            $names['page'] = '[page]';
        }

        $response->{Arr::last(explode('\\', get_class($response))) == 'Response' ? 'setContent': 'setData'}([$class, $data, $names]);

        return $response;
    }
}
