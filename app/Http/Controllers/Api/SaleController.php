<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::all();
        return ApiResponse::successResponse('sales fetched successfully', $sales, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string',
            'quantity' => 'required|integer',
            'price' => 'required|integer',
            'total' => 'required|integer',
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        try{
            $sale = Sale::create([
                'name' => $request->name,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'total' => $request->total,
            ]);
            return ApiResponse::successResponse('sale created successfully', $sale, 201);
        }catch(\Exception $e){
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sale = Sale::find($id);
        if(!$sale){
            return ApiResponse::errorResponse('sale not found', null, 404);
        }
        return ApiResponse::successResponse('sale fetched successfully', $sale, 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sale = Sale::find($id);
        if(!$sale){
            return ApiResponse::errorResponse('sale not found', null, 404);
        }
        $sale->delete();
        return ApiResponse::successResponse('sale deleted successfully', null, 200);
    }
}
