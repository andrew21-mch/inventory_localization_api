<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\OutOfStock;
use Illuminate\Http\Request;

class OutOfStockController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $out_of_stocks = OutOfStock::with('component')->get();
        return ApiResponse::successResponse('out of stocks fetched successfully', $out_of_stocks, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validators = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'component_id' => 'required|integer',
            'quantity' => 'required|integer',
            'reason' => 'required|string',
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        try{
            $out_of_stock = OutOfStock::create([
                'component_id' => $request->component_id,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
            ]);
            return ApiResponse::successResponse('out of stock created successfully', $out_of_stock, 201);
        }catch(\Exception $e){
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
