<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Component;
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
        $sales = Sale::with('component')->get();
        return ApiResponse::successResponse('sales fetched successfully', $sales, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'component_id' => 'required|integer',
            'quantity' => 'required|integer',
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        $component = Component::find($request->component_id);
        if(!$component){
            return ApiResponse::errorResponse('component not found', null, 404);
        }

        \DB::beginTransaction();
        try{
            $sale = Sale::create([
                'component_id' => $request->component_id,
                'quantity' => $request->quantity,
                'total_price' => $request->quantity * $component->price_per_unit,
                'buyer' => $request->buyer
            ]);
            $component->quantity -= $request->quantity;
            $component->save();
            \DB::commit();
            return ApiResponse::successResponse('sale created successfully', $sale, 201);
        }catch(\Exception $e){
            \DB::rollBack();
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

    public function search(Request $request)
    {
        $sales = Sale::with('component')->where('quantity', 'like', '%'.$request->search.'%')->orWhereHas('component', function($query) use ($request){
            $query->where('name', 'like', '%'.$request->search.'%')
            ->orWhere('description', 'like', '%'.$request->search.'%');
        })->get();
        return ApiResponse::successResponse('sales searched successfully', $sales, 200);
    }

    public function filterSales(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        $sales = Sale::with('component')->whereBetween('created_at', [$request->from, $request->to])->get();

        return ApiResponse::successResponse('sales statistics fetched successfully', $sales, 200);
    }
}
