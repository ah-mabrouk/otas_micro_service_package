<?php

namespace Solutionplus\MicroService\Http\Controllers;

use Solutionplus\MicroService\Http\Controllers\Controller;
use Solutionplus\MicroService\Http\Requests\MicroServiceMapUpdateRequest;

class UpdateMicroserviceSecretController extends Controller
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  MicroServiceMapUpdateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(MicroServiceMapUpdateRequest $request)
    {
        $request->updateMicroServiceMap();

        return response([
            'message' => 'updated',
        ]);
    }
}
