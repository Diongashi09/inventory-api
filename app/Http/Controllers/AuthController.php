<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Role;
use App\Models\Client;

class AuthController extends Controller
{
    // public function register(Request $request)
    // {
    //     $data = $request->validate([
    //         'name'                  => 'required|string|max:255',
    //         'email'                 => 'required|string|email|unique:users',
    //         'password'              => 'required|string|confirmed|min:6',
    //         'role_id'               => 'required|exists:roles,id',
    //     ]);

    //     $role = Role::find($data['role_id']);
    //     $forbiddenRoles = ['admin', 'manager'];


    //     if (in_array(strtolower($role->name), $forbiddenRoles)) {
    //         return response()->json([
    //             'message' => 'You are not allowed to register as an admin or manager.'
    //         ], 403);
    //     }

    //     $user = User::create([
    //         'name'      => $data['name'],
    //         'email'     => $data['email'],
    //         'password'  => Hash::make($data['password']),
    //         'role_id'   => $data['role_id'],
    //     ]);

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'access_token' => $token,
    //         'token_type'   => 'Bearer',
    //         'user'         => $user->load('role'),
    //     ], 201);
    // }


    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|unique:users',
            'password'              => 'required|string|confirmed|min:6',
        ]);

        // 2) Find your “client” role (must already exist in roles table)
        $clientRole = Role::where('name','client')->firstOrFail();

        // 3) Create the User as a client
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role_id'   => $clientRole->id,
        ]);

        // 4) Create the Client record and link it
        Client::create([
            'user_id'         => $user->id,
            'name'            => $user->name,
            // you can customize these defaults or pull from the request if you extend your form
            'client_type'     => 'individual',
            'contact_person'  => null,
            'phone'           => null,
            'email'           => $user->email,
            'address'         => null,
            'additional_info' => null,
        ]);

        // 5) Issue token and respond
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user->load('role'),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user' => $user->load('role')
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email'),
            function ($user, $token) {
                // Send custom notification with API reset link
                $user->notify(new \App\Notifications\CustomResetPasswordNotification($token));
            }
        );

        return $status === Password::RESET_LINK_SENT 
        ? response()->json(['message' => 'Reset link sent.'])
        : response()->json(['message' => 'Unable to send reset link.'], 400);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email'                 => 'required|email|exists:users,email',
            'token'                 => 'required|string',
            'password'              => 'required|string|confirmed|min:6',
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message'=>__($status)]);
        }

        throw ValidationException::withMessages([
            'token' => [__($status)],
        ]);
    }
}
