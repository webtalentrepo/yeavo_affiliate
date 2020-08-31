<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Role as RoleResource;
use App\Role;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return RoleResource::collection(Role::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles|min:2'
        ], [
            'name.required' => 'Role name is required.',
        ]);

        $params = $this->makingParams($request->all());

        $role = new Role();
        $role->fill($params);
        $role->save();

        return response()->json(['result' => 'success']);
    }

    /**
     * Reset save params.
     * @param $params
     * @return mixed
     */
    private function makingParams($params)
    {
        foreach ($params as $key => $val) {
            if (is_null($val) && $key != 'name') {
                $params[$key] = '';
            }

            if ($key == 'is_admin') {
                if ($val) {
                    $params[$key] = 1;
                } else {
                    $params[$key] = 0;
                }
            }
        }

        return $params;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Role $role)
    {
        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Role $role
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Role $role)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name,' . $role->id . '|min:2'
        ], [
            'name.required' => 'Role Name is required.'
        ]);

        $params = $this->makingParams($request->all());

        $role->fill($params);
        $role->save();

        return response()->json(['result' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Role $role
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Role $role)
    {
        if (!$role->users()->count()) {
            $role->delete();
        } else {
            return response()->json(['message' => 'You cannot delete this role. There are users exist who are assigned to this role.'], 422);
        }

        return response()->json(['result' => 'success'], 200);
    }
}
