<?php

namespace Solutionplus\MicroService\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Solutionplus\MicroService\Helpers\MsHttp;

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
        if (config('microservice.disable_package_middleware') && ! App::environment('production')) return $next($request);

        if (! MsHttp::decodeRequest()) abort(405, 'forbidden action');

        return $next($request);
    }
}
