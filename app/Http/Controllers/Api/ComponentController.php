<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\OutOfStock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ComponentController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $components = Component::with('supplier', 'led')->get();

        return ApiResponse::successResponse('components fetched successfully', $this->formatComponents($components), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string',
            'quantity' => 'required|integer',
            'unique_identifier' => 'required|string',
            'description' => 'required|string',
            'price_per_unit' => 'required|decimal:0,2',
            'cost_price_per_unit' => 'required|decimal:0,2'
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        \DB::beginTransaction();
        try {
            if ($request->supplier_id) {
                $supplier = Supplier::find($request->supplier_id);
                if (!$supplier) {
                    return ApiResponse::errorResponse('supplier not found', null, 404);
                }
            }

            $imagePath = null;

            if ($request->has('image')) {
                $imageData = $request->input('image');
                $image = base64_decode($imageData);
                $imagePath = 'items/' . uniqid() . '.jpg'; // Generate a unique filename
                Storage::disk('public')->put($imagePath, $image);
            }


            if (!$request->supplier_id && $request->add_supplier) {
                $supplier = Supplier::create([
                    'name' => $request->supplier_name,
                    'phone' => $request->supplier_phone,
                    'email' => $request->supplier_email,
                    'address' => $request->supplier_address,
                ]);
            }
            $component = Component::create([
                'name' => $request->name,
                'identifier' => $request->unique_identifier,
                'quantity' => $request->quantity,
                'price_per_unit' => $request->price_per_unit,
                'cost_price_per_unit' => $request->cost_price_per_unit,
                'image' => $imagePath,
                'slug' => self::createSlug($request->name),
                'description' => $request->description,
                'supplier_id' => $request->supplier_id ?? $supplier->id,
                'led_id' => $request->location
            ]);


            $ledTriggered = false;
            if ($request->led_id) {
                $ledTriggered = LEDController::testLed($request->led_id);
            }

            if ($component->quantity <= 10) {
                try {
                    $outOfStock = OutOfStock::where('component_id', $component->id)->first();
                    if ($outOfStock) {
                        $outOfStock->update([
                            'component_id' => $component->id,
                            'supplier_id' => $component->supplier_id,
                        ]);
                    } else {
                        OutOfStockController::store($component->id, $component->supplier_id);
                    }
                } catch (\Exception $e) {
                    return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
                }
            }

            \DB::commit();
            if ($ledTriggered) {
                return ApiResponse::successResponse('component created successfully', $component, 201);
            } else {
                return ApiResponse::successResponse('component created successfully, but led not triggered', $component, 201);
            }

        } catch (\Exception $e) {
            \DB::rollback();
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $component = Component::find($id);
        if (!$component) {
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
        if (!$component) {

            return ApiResponse::errorResponse('component not found', null, 404);
        }

        $name = $component->image;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
            $oldImage = public_path('/images/') . $component->image;
            if (file_exists($oldImage)) {
                @unlink($oldImage);
            }
        }

        \DB::beginTransaction();
        try {

            // update using request->all() will update the slug
            $component->update([
                'name' => $request->name ?? $component->name,
                'quantity' => $request->quantity ?? $component->quantity,
                'price_per_unit' => $request->price_per_unit ?? $component->price_per_unit,
                'cost_price_per_unit' => $request->cost_price_per_unit ?? $component->cost_price_per_unit,
                'image' => $name,
                'description' => $request->description ?? $component->description,
                'supplier_id' => $request->supplier_id ?? $component->supplier_id,
                'led_id' => $request->location ?? $component->led_id
            ]);
            \DB::commit();
            return ApiResponse::successResponse('component updated successfully', $component, 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $component = Component::with('led', 'sales', 'outOfStocks')->where('id', $id)->first();
        if (!$component) {
            ApiResponse::errorResponse('component not found', null, 404);
        }

        \DB::beginTransaction();
        try {

            if ($component->sales->count() > 0 || $component->outOfStocks->count() > 0) {
                return ApiResponse::errorResponse('component cannot be deleted because it has sales or out of stock', null, 422);
            }

            $component->delete();
            \DB::commit();
            return ApiResponse::successResponse('component deleted successfully', null, 200);
        } catch (\Exception $e) {
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
            ->orWhere('identifier', 'LIKE', "%{$request->search}%")
            ->orWhere('price_per_unit', 'LIKE', "%{$request->search}%")
            ->orWhere('cost_price_per_unit', 'LIKE', "%{$request->search}%")
            ->orWhereHas('supplier', function ($query) use ($request) {
                $query->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('phone', 'LIKE', "%{$request->search}%");
            })->with('led')->get();

        if ($components) {
            foreach ($components as $component) {
                if ($component->quantity <= 10) {
                    try {
                        $outOfStock = OutOfStock::where('component_id', $component->id)->first();
                        if ($outOfStock) {
                            $outOfStock->update([
                                'component_id' => $component->id,
                                'supplier_id' => $component->supplier_id,
                            ]);
                        } else {
                            OutOfStockController::store($component->id, $component->supplier_id);
                        }
                    } catch (\Exception $e) {
                        return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
                    }
                }
            }
        }
        return ApiResponse::successResponse('component fetched successfully', $this->formatComponents($components), 200);

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

    public static function formatComponents($components)
    {
        $formattedComponents = [];
        foreach ($components as $component) {
            $formattedComponents[] = [
                'id' => $component->id,
                'name' => $component->name,
                'identifier' => $component->identifier,
                'quantity' => $component->quantity,
                'price_per_unit' => $component->price_per_unit,
                'cost_price_per_unit' => $component->cost_price_per_unit,
                'description' => $component->description,
                'image' => $component->image,
                'slug' => $component->slug,
                'supplier' => $component->supplier,
                'led' => $component->led,
                'created_at' => $component->created_at,
                'updated_at' => $component->updated_at,
                'status' => $component->quantity <= 10 ? 'low' : 'high'
            ];
        }

        return $formattedComponents;
    }
}
