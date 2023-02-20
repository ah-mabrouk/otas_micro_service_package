<?php

namespace Solutionplus\MicroService\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Solutionplus\MicroService\Models\MicroServiceMap;

class MicroServiceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        //request data
        $request_url = $request->url();
        $request_token = $request->bearerToken();

        //check process
        $exist = MicroServiceMap::where('base_url', '=', $request_url)
            ->where('source_token', '=', $request_token);

            if($exist->count() > 0)
            {
                return $next($request);
            }else{
                abort(403, 'Un-authenticated');
            }

    }
}
