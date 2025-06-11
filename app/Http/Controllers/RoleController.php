<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(Role::class, 'role');
    }

    public function index()
    {
        return Role::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|unique:roles',
            'description' => 'nullable|string',
        ]);

        return response()->json(Role::create($data), 201);
    }

    public function show(Role $role)
    {
        return $role;
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'        => 'sometimes|required|string|unique:roles,name,'.$role->id,
            'description' => 'nullable|string',
        ]);

        $role->update($data);
        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(null, 204);
    }
}
