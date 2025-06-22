<?php

namespace Solutionplus\MicroService\Http\Controllers;

use Exception;
use Solutionplus\MicroService\Models\MicroServiceMap;
use Solutionplus\MicroService\Http\Controllers\Controller;
use Solutionplus\MicroService\Http\Requests\MicroServiceMapStoreRequest;
use Solutionplus\MicroService\Http\Requests\MicroServiceMapUpdateRequest;

class MicroServiceMapController extends Controller
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
        $this->middleware('micro-service-establish-connection')->only('store');
        $this->middleware('micro-service')->only('update');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Solutionplus\MicroService\Http\Requests\RoleStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MicroServiceMapStoreRequest $request)
    {
        try {
            $request->storeMicroServiceMap();
        } catch (Exception $exception) {
            return response([
                'message' => 'not allowed',
            ], 405);
        }
        return response([
            'message' => 'established',
            'secret' => config('microservice.local_secret'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param MicroServiceMapUpdateRequest $request
     * @param MicroserviceMap $micro_service
     * @return \Illuminate\Http\Response
     */
    public function update(MicroServiceMapUpdateRequest $request, MicroServiceMap $micro_service)
    {
        $request->updateMicroServiceMap();

        return response([
            'message' => 'updated',
        ]);
    }   
}
