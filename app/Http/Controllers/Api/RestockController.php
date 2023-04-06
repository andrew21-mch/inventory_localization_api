<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\OutOfStock;
use App\Models\Restock;
use Illuminate\Http\Request;

class RestockController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get restock with component and count the quantity
        $restocks = Restock::with('component')->get();

        $restocks = $restocks->groupBy('component_id')->map(function ($item) {
            return [
                'component_id' => $item[0]->component_id,
                'component_name' => $item[0]->component->name,
                'quantity' => $item->sum('quantity'),
            ];
        });


        return ApiResponse::successResponse('restocks fetched successfully', $restocks, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validators = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'component_id' => 'required|integer',
            'quantity' => 'required|integer',
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        $item = Component::find($request->component_id);
        if(!$item){
            return ApiResponse::errorResponse('component not found', null, 404);
        }

        \DB::beginTransaction();
        try{
            $item->quantity += $request->quantity;
            $item->save();
            Restock::create([
                'component_id' => $request->component_id,
                'quantity' => $request->quantity,
            ]);
            $out_of_stock = OutOfStock::where('component_id', $request->component_id)->first();
            if($out_of_stock){
                $out_of_stock->delete();
            }
            \DB::commit();
            return ApiResponse::successResponse('component restocked successfully', $item, 201);
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
        $restock = Restock::find($id);
        if(!$restock){
            return ApiResponse::errorResponse('restock not found', null, 404);
        }

        \DB::beginTransaction();
        try{
            $component = Component::find($restock->component_id);
            $component->quantity -= $restock->quantity;
            $component->save();
            $restock->delete();
            \DB::commit();
            return ApiResponse::successResponse('restock deleted successfully', null, 200);
        }catch(\Exception $e){
            \DB::rollBack();
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }
}
