<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ValidatesRequestsJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ValidatesRequestsJson;
    //
    public function register(Request $request)
    {
        $validated = $this->validateJson($request, [
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,organizer,customer',
        ]);

        if ($validated instanceof \Illuminate\Http\JsonResponse) {
            return $validated;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role']
        ]);

        return response()->json(['user' => $user, 'token' => $user->createToken('API Token')->plainTextToken]);
    }



    public function login(Request $request)
    {



        $validated = $this->validateJson($request, [
            'email'    => 'required|email',
            'password' => 'required'
        ]);



        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid login details.'
            ], 400);
        }

        if ($validated instanceof \Illuminate\Http\JsonResponse) {
            return $validated;
        }

        // delete old tokens – optional but good
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $user
        ]);
    }

    public function logout(Request $request)
    {
         $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }



    // public function logout(Request $request)
    // {
    //     // Delete the current access token being used
       

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Logged out',
    //     ], 200);
    // }
}
