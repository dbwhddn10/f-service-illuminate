<?php

namespace Dbwhddn10\FService\Illuminate\Http;

class RequestInputValueCastingMiddleware
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
