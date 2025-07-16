<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request)
    {
        // return User::with('role')->get();
        $clientRoleId = Role::where('name','Client')->value('id');

        $query = User::query();

        if($clientRoleId){
            $query->where('role_id','!=',$clientRoleId);
        }

        if ($request->has('search') && !empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        $employees = $query->with('role')->get();

        return response()->json($employees);
    }

    public function show(User $user)
    {
        return $user->load('role');
    }

    public function createManager(Request $request)
    {
        // Ensure the authenticated user is an admin
        $authUser = $request->user();
        if (strtolower($authUser->role->name) !== 'admin') {
            return response()->json(['message' => 'Only admins can create a manager.'], 403);
        }

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $managerRole = Role::where('name', 'manager')->firstOrFail();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => \Hash::make($data['password']),
            'role_id'  => $managerRole->id,
        ]);

        return response()->json($user->load('role'), 201);
    }


    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'sometimes|required|string',
            'email'    => 'sometimes|required|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|confirmed|min:6',
            'role_id'  => 'sometimes|exists:roles,id',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}