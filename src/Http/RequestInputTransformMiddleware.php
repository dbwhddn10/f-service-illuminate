<?php

namespace Illuminate\Extend\Http;

class RequestInputTransformMiddleware
{
    public function handle($request, $next)
    {
        foreach ( $request->all() as $key => $value )
        {
            if ( $value === 'null' )
            {
                $value = null;
            }
            else if ( $value === 'false' )
            {
                $value = false;
            }
            else if ( $value === 'true' )
            {
                $value = true;
            }

            $request->offsetSet($key, $value);
        }

        return $next($request);
    }
}
