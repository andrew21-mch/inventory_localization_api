<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\OutOfStock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComponentController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $components = Component::with('supplier')->get();
        return ApiResponse::successResponse('components fetched successfully', $components, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string',
            'quantity' => 'required|integer',
            'description' => 'required|string',
            'price_per_unit' => 'required',
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        $name = null;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
        }

        \DB::beginTransaction();
        try{
            if($request->supplier_id){
                $supplier = Supplier::find($request->supplier_id);
                if(!$supplier){
                    return ApiResponse::errorResponse('supplier not found', null, 404);
                }
            }

            if(!$request->supplier_id && $request->add_supplier){
                $supplier = Supplier::create([
                    'name' => $request->supplier_name,
                    'phone' => $request->supplier_phone,
                    'email' => $request->supplier_email,
                    'address' => $request->supplier_address,
                ]);
            }
            $component = Component::create([
                'name' => $request->name,
                'quantity' => $request->quantity,
                'price_per_unit' => $request->price_per_unit,
                'cost_price_per_unit' => $request->cost_price_per_unit,
                'image' => $name,
                'slug' => self::createSlug($request->name),
                'description' => $request->description,
                'supplier_id' => $request->supplier_id ?? $supplier->id,

            ]);

            \DB::commit();

            return ApiResponse::successResponse('component created successfully', $component, 201);
        }catch(\Exception $e){
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $component = Component::find($id);
        if(!$component){
            return ApiResponse::errorResponse('component not found', null, 404);
        }

        return ApiResponse::successResponse('component fetched successfully', $component, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $component = Component::find($id);
        if(!$component){

            return ApiResponse::errorResponse('component not found', null, 404);
        }

        $name = $component->image;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
            $oldImage = public_path('/images/').$component->image;
            if(file_exists($oldImage)){
                @unlink($oldImage);
            }
        }

        \DB::beginTransaction();
        try{
            $component->update([
                'name' => $request->name,
                'quantity' => $request->quantity,
                'price_per_unit' => $request->price,
                'cost_price_per_unit' => $request->cost_price,
                'description' => $request->description,
                'image' => $name,
                'slug' => \Str::slug($request->name),

            ]);
            \DB::commit();
            return ApiResponse::successResponse('component updated successfully', $component, 200);
        }catch(\Exception $e){
            \DB::rollBack();
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $component = Component::find($id);
        if(!$component){
            ApiResponse::errorResponse('component not found', null, 404);
        }

        \DB::beginTransaction();
        try{
            $component->delete();
            \DB::commit();
            return ApiResponse::successResponse('component deleted successfully', null, 200);
        }catch(\Exception $e){
            \DB::rollBack();
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }

    /**
     * search for a component
     */
    public function search(Request $request)
    {
        $components = Component::where('name', 'LIKE', "%{$request->search}%")
        ->orWhere('description', 'LIKE', "%{$request->search}%")
        ->orWhere('quantity', 'LIKE', "%{$request->search}%")
        ->orWhere('price_per_unit', 'LIKE', "%{$request->search}%")
        ->orWhere('cost_price_per_unit', 'LIKE', "%{$request->search}%")
        ->orWhereHas('supplier', function($query) use ($request){
            $query->where('name', 'LIKE', "%{$request->search}%")
            ->orWhere('phone', 'LIKE', "%{$request->search}%");
        })->get();

        if($components){
            foreach($components as $component){
                if($component->quantity <= 10){
                    try
                    {
                        $outOfStock = OutOfStock::where('component_id', $component->id)->first();
                        if($outOfStock){
                            $outOfStock->update([
                                'component_id' => $component->id,
                                'supplier_id' => $component->supplier_id,
                            ]);
                        }else{
                            OutOfStockController::store($component->id, $component->supplier_id);
                        }
                    }
                    catch(\Exception $e)
                    {
                        return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
                    }
                }
            }
        }
        return ApiResponse::successResponse('component fetched successfully', $components, 200);

    }

    /**
     * create slug
     */
    public static function createSlug(string $string)
    {
        $slug = \Str::slug($string);
        $count = Component::where('slug', 'LIKE', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }
}
