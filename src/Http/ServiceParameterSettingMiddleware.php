<?php

namespace Illuminate\Extend\Http;

use Illuminate\Support\Arr;

class ServiceParameterSettingMiddleware
{
    public function handle($request, $next)
    {
        $response = $next($request);
        $content  = $response->getOriginalContent();
        $class    = $content[0];
        $data     = Arr::get($content, 1, []);
        $names    = Arr::get($content, 2, []);

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

        if ( preg_match('/ListingService$/', $class) )
        {
            $data['expands']    = Arr::get($request->all(), 'expands', '');
            $data['fields']     = Arr::get($request->all(), 'fields', '');
            $data['order_by']   = Arr::get($request->all(), 'order_by', '');
            $names['expands']   = '[expands]';
            $names['fields']    = '[fields]';
            $names['order_by']  = '[order_by]';
        }
        else if ( preg_match('/PagingService$/', $class) )
        {
            $data['cursor_id']  = Arr::get($request->all(), 'cursor_id', '');
            $data['expands']    = Arr::get($request->all(), 'expands', '');
            $data['fields']     = Arr::get($request->all(), 'fields', '');
            $data['limit']      = Arr::get($request->all(), 'limit', '');
            $data['order_by']   = Arr::get($request->all(), 'order_by', '');
            $data['page']       = Arr::get($request->all(), 'page', '');
            $names['cursor_id'] = '[cursor_id]';
            $names['expands']   = '[expands]';
            $names['fields']    = '[fields]';
            $names['limit']     = '[limit]';
            $names['order_by']  = '[order_by]';
            $names['page']      = '[page]';
        }
        else if ( preg_match('/FindingService$/', $class) )
        {
            $data['expands']  = Arr::get($request->all(), 'expands', '');
            $data['fields']   = Arr::get($request->all(), 'fields', '');
            $data['id']       = $request->route('id');
            $names['expands'] = '[expands]';
            $names['fields']  = '[fields]';
            $names['id']      = $request->route('id');
        }
        else if ( preg_match('/UpdatingService$/', $class) )
        {
            $data['id']  = $request->route('id');
            $names['id'] = $request->route('id');
        }
        else if ( preg_match('/DeletingService$/', $class) )
        {
            $data['id']  = $request->route('id');
            $names['id'] = $request->route('id');
        }

        $response->setContent([$class, $data, $names]);

        return $response;
    }
}
