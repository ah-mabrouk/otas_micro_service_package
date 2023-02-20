<?php

namespace Solutionplus\MicroService\Http\Controllers;

use Solutionplus\MicroService\Models\Role;
use Solutionplus\MicroService\Filters\RoleFilter;
use Solutionplus\MicroService\Http\Resources\RoleResource;
use Solutionplus\MicroService\Http\Requests\RoleStoreRequest;
use Solutionplus\MicroService\Http\Requests\RoleUpdateRequest;
use Solutionplus\MicroService\Http\Resources\RoleSimpleResource;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Solutionplus\MicroService\Admin\RoleFilter  $filters
     * @return \Illuminate\Http\Response
     */
    public function index(RoleFilter $filters)
    {
        $paginationLength = pagination_length(Role::class);
        $roles = Role::filter($filters)->paginate($paginationLength);
        return RoleSimpleResource::collection($roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Solutionplus\MicroService\Http\Requests\RoleStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleStoreRequest $request)
    {
        $role = $request->storeRole();
        return response([
            'message' => __('mabrouk/permission/roles.store'),
            'role' => new RoleResource($role),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Solutionplus\MicroService\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        return response([
            'role' => new RoleResource($role),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Solutionplus\MicroService\Http\Requests\RoleUpdateRequest  $request
     * @param  \Solutionplus\MicroService\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(RoleUpdateRequest $request, Role $role)
    {
        $role = $request->updateRole();
        return response([
            'message' => __('mabrouk/permission/roles.update'),
            'role' => new RoleResource($role),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Solutionplus\MicroService\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        $role = $role->remove();
        return response([
            'message' => $role->response['message'],
        ], $role->response['response_code']);
    }
}
