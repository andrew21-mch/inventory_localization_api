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
        $componets = Component::all();
        return ApiResponse::successResponse('components fetched successfully', $componets, 200);
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

        if($request->hasFile('image')){
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
        }

        try{
            $component = Component::create([
                'name' => $request->name,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'image' => $name,
                'slug' => self::createSlug($request->name),
                'total' => $request->total,
            ]);
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

        $validators = Validator::make($request->all(), [
            'name' => 'required|string',
            'quantity' => 'required|integer',
            'price' => 'required|integer',
            'total' => 'required|integer',
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

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

        try{
            $component->update([
                'name' => $request->name,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'image' => $name,
                'slug' => self::createSlug($request->name),
                'total' => $request->total,
            ]);

            return ApiResponse::successResponse('component updated successfully', $component, 200);
        }catch(\Exception $e){
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

        try{
            $component->delete();
            return ApiResponse::successResponse('component deleted successfully', null, 200);
        }catch(\Exception $e){
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
        ->orWhere('price', 'LIKE', "%{$request->search}%")
        ->orWhereHas('suppliers', function($query) use ($request){
            $query->where('name', 'LIKE', "%{$request->search}%")
            ->orWhere('email', 'LIKE', "%{$request->search}%")
            ->orWhere('phone', 'LIKE', "%{$request->search}%")
            ->orWhere('address', 'LIKE', "%{$request->search}%");
        })->get();

        if($components){
            foreach($components as $component){
                if($component->quantiry < 10){
                    OutOfStock::create([
                        'component_id' => $component->id,
                        'quantity' => $component->quantity,
                        'date' => date('Y-m-d'),
                    ]);
                }
            }
        }
        return ApiResponse::successResponse('component fetched successfully', $component, 200);

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
