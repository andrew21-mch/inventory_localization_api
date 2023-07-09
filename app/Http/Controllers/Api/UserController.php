<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    use ApiResponse;

    public function index()
    {
        $users = \App\Models\Supplier::all();
        return ApiResponse::successResponse('suppliers fetched successfully', $users, 200);
    }
    public function show(string $id)
    {
        $user = User::find($id);
        if(!$user){
            return ApiResponse::errorResponse('user not found', null, 404);
        }
    }

    public function profile(){
        $profile = auth()->user();
        return ApiResponse::successResponse('Fetched Profile', $profile, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        if(!$user){
            return ApiResponse::errorResponse('user not found', null, 404);
        }

        try{
            $imagePath = null;

            if ($request->has('image')) {
                $imageData = $request->input('image');
                $image = base64_decode($imageData);
                $imagePath = 'profile/' . uniqid() . '.jpg'; // Generate a unique filename
                Storage::disk('public')->put($imagePath, $image);
            }
            $user->update([
                'name' => $request->name,
                'phone' => $request-> phone,
                'email' => $request->email,
                'profile_url' => $imagePath
            ]);
            return ApiResponse::successResponse('user updated successfully', $user, 200);
        }catch(\Exception $e){
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }


    /**
     * Update password  for the specified resource in storage.
     * (current password is required)
     */
    public function updatePassword(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|confirmed'
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        $user = auth()->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return ApiResponse::errorResponse('sorry, old password is incorrect', null, 401);
        }

        try{
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return ApiResponse::successResponse('password updated successfully', $user, 200);
        }catch(\Exception $e){
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }

}
