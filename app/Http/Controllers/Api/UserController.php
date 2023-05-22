<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

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
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if(!$user){
            return ApiResponse::errorResponse('user not found', null, 404);
        }

        try{
            $user->update($request->all());
            return ApiResponse::successResponse('user updated successfully', $user, 200);
        }catch(\Exception $e){
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
