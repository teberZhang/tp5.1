<?php

namespace app\http\middleware;

class Check
{
    public function handle($request, \Closure $next)
    {
        if ($request->param('name') == 'think') {
            //return redirect('index/think');
            $request->ware = 'dfsadfasfdsa';
        }

        return $next($request);
    }
}
