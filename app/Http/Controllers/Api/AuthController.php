<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|string',
        ]);

        if ($validators->fails()) {
            return response()->json([
                'message' => 'some fields are not valid',
                'errors' => $validators->errors()
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'error' => 'unauthorized',
                'success' => false,
                'message' => 'sorry, we can\'t find this user'
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'unauthorized',
                'success' => false,
                'message' => 'sorry, your password is incorrect'
            ], 401);
        }

        $tokenResult = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'login successful',
            'user' => $user,
            'access_token' => $tokenResult
        ]);
    }

    public function register(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        if ($validators->fails()) {
            return response()->json([
                'message' => 'some fields are not valid',
                'errors' => $validators->errors()
            ], 422);
        }

        try{
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password)
            ]);

            $user->save();

            return response()->json([
                'message' => 'Successfully created user!',
                'user' => $user
            ], 201);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to create user!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
