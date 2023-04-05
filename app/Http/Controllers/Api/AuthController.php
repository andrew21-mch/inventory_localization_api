<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;
    public function login(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|string',
        ]);

        if ($validators->fails()) {
           ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return ApiResponse::errorResponse('sorry, we can\'t find this user', null, 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return ApiResponse::errorResponse('sorry, password is incorrect', null, 401);
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
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        try{
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password)
            ]);

            $user->save();

            return ApiResponse::successResponse('user created successfully', $user, 201);
        }catch(\Exception $e){
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return ApiResponse::successResponse('logged out successfully', null, 200);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
