<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::with('components')->get();
        return ApiResponse::successResponse('suppliers fetched successfully', $suppliers, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validators = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        try{
            $supplier = Supplier::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
            return ApiResponse::successResponse('supplier created successfully', $supplier, 201);
        }catch(\Exception $e){
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supplier = Supplier::with('components')->find($id);
        if(!$supplier){
            return ApiResponse::errorResponse('supplier not found', null, 404);
        }
        return ApiResponse::successResponse('supplier fetched successfully', $supplier, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validators = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        try{
            $supplier = Supplier::find($id);
            if(!$supplier){
                return ApiResponse::errorResponse('supplier not found', null, 404);
            }
            $supplier->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
            return ApiResponse::successResponse('supplier updated successfully', $supplier, 200);
        }catch(\Exception $e){
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $supplier = Supplier::find($id);
        if(!$supplier){
            return ApiResponse::errorResponse('supplier not found', null, 404);
        }
        $supplier->delete();
        return ApiResponse::successResponse('supplier deleted successfully', null, 200);
    }
}
