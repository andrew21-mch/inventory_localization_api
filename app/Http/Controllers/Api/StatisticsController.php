<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\OutOfStock;
use App\Models\Restock;
use App\Models\Sale;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function statistics()
    {
        $total_components = Component::count();

        $component_with_their_quantity = Component::select('id', 'name', 'quantity')->get();

        $restocks_quantity_with_dates = Restock::select('quantity', 'created_at')->get();

        $out_of_stocks_with_current_quantity = OutOfStock::with('component')->get();

        $out_of_stocks = $out_of_stocks_with_current_quantity->map(function ($item) {
            return [
                'component_id' => $item->component_id,
                'component_name' => $item->component->name,
                'quantity' => $item->component->quantity,
            ];
        });

        $generated_profit_per_component = Sale::with('component')->get();

        $generated_profit_per_component = $generated_profit_per_component->groupBy('component_id')->map(function ($item) {
            return [
                'component_id' => $item[0]->component_id,
                'component_name' => $item[0]->component->name,
                'quantity' => $item->sum('quantity'),
                'profit' => $this->calculateProfie($item[0]->component->cost_price_per_unit, $item[0]->component->price_per_unit, $item->sum('quantity')),
                'expenditure' => $this->calculateExpenditure(),

            ];
        });

        $restocks = Restock::with('component')->get();

        $restocks = $restocks->groupBy('component_id')->map(function ($item) {
            return [
                'component_id' => $item[0]->component_id,
                'component_name' => $item[0]->component->name,
                'quantity' => $item->sum('quantity'),
            ];
        });

        $total_profits = $generated_profit_per_component->sum('profit');


        return ApiResponse::successResponse('statistics fetched successfully', [
            'restocks' => $restocks,
            'out_of_stocks' => $out_of_stocks,
            'total_components' => $total_components,
            'component_with_their_quantity' => $component_with_their_quantity,
            'restocks_quantity_with_dates' => $restocks_quantity_with_dates,
            'generated_profit_per_component' => $generated_profit_per_component,
            'total_profit' => $total_profits,
            'expenditure' => $this->calculateExpenditure(),
        ], 200);
    }

    public function sales_statistics()
    {
        $sales = Sale::with('component')->get();

        $sales = $sales->groupBy('component_id')->map(function ($item) {
            return [
                'component_id' => $item[0]->component_id,
                'component_name' => $item[0]->component->name,
                'quantity' => $item->sum('quantity'),
                'total_price' => $item->sum('total_price'),
                'profit' => $this->calculateProfie($item[0]->component->cost_price_per_unit, $item[0]->component->price_per_unit, $item->sum('quantity')),
                'expenditure' => $this->calculateExpenditure(),
            ];
        })->values()->toArray();

        return ApiResponse::successResponse('sales statistics fetched successfully', $sales, 200);

    }

    public function sales_statistics_by_date(Request $request)
    {
        $validators = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        $sales = Sale::with('component')->whereBetween('created_at', [$request->from, $request->to])->get();

        $sales = $sales->groupBy('component_id')->map(function ($item) {
            return [
                'component_id' => $item[0]->component_id,
                'component_name' => $item[0]->component->name,
                'quantity' => $item->sum('quantity'),
                'total_price' => $item->sum('total_price'),
                'profit' => $this->calculateProfie($item[0]->component->cost_price_per_unit, $item[0]->component->price_per_unit, $item->sum('quantity')),
                'expenditure' => $this->calculateExpenditure(),
            ];
        });

        return ApiResponse::successResponse('sales statistics fetched successfully', $sales, 200);
    }

    public function restocks_statistics_by_date(Request $request)
    {
        $validators = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        $restocks = Restock::with('component')->whereBetween('created_at', [$request->from, $request->to])->get();

        $restocks = $restocks->groupBy('component_id')->map(function ($item) {
            return [
                'component_id' => $item[0]->component_id,
                'component_name' => $item[0]->component->name,
                'quantity' => $item->sum('quantity'),
            ];
        });

        return ApiResponse::successResponse('restocks statistics fetched successfully', $restocks, 200);
    }

    public function calculateProfie($cost, $price, $quantity)
    {
        return ($price * $quantity) - ($cost * $quantity);
    }

    public function calculateExpenditure()
    {
        $components = Component::all();
        $sales = Sale::with('component')->get();

        $total = 0;

        foreach ($components as $component) {
            $total = $component->cost_price_per_unit * $component->quantity;
        }


        // return $sales;
        $soledExpenseTotal = 0;
        foreach ($sales as $sale) {

            $soledExpenseTotal += $sale->component->cost_price_per_unit * $sale->quantity;

        }

        $total = $total + $soledExpenseTotal;

        return $total;
    }
}
