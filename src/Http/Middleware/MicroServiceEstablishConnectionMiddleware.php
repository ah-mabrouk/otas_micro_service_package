<?php

namespace Solutionplus\MicroService\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Solutionplus\MicroService\Models\MicroServiceMap;

class MicroServiceEstablishConnectionMiddleware
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
        // ckeck by project_secret

    }
}
