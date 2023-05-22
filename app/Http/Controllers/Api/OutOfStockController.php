<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\OutOfStock;
use App\Models\Supplier;
use Illuminate\Http\Request;

class OutOfStockController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $out_of_stocks = OutOfStock::with('component', 'supplier')->get();
        return ApiResponse::successResponse('out of stocks fetched successfully', $out_of_stocks, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public static function store($component_id, $supplier_id=null)
    {
        try{
            if($supplier_id){
                $supplier = Supplier::find($supplier_id);
                if(!$supplier){
                    return ['status' => false, 'message' => 'supplier not found'];
                }
            }
            $out_of_stock = OutOfStock::create([
                'component_id' => $component_id,
                'supplier_id' => $supplier_id,
            ]);
            return ['status' => true, 'message' => 'out of stock created successfully', 'data' => $out_of_stock];
        }catch(\Exception $e){
            return ['status' => false, 'message' => 'something went wrong', 'data' => $e->getMessage()];
        }
    }

    public function search(Request $request)
    {
        $outOfStocks = OutOfStock::whereHas('component', function($query) use ($request){
            $query->where('name', 'LIKE', "%{$request->search}%")
            ->orWhere('description', 'LIKE', "%{$request->search}%");
        })->orWhereHas('supplier', function($query) use ($request){
            $query->where('name', 'LIKE', "%{$request->search}%")
            ->orWhere('phone', 'LIKE', "%{$request->search}%");
        })->with('component', 'supplier')->get();
        return ApiResponse::successResponse('component fetched successfully', $outOfStocks, 200);

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
