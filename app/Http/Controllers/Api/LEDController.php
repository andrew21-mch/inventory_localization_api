<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Led;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LEDController extends Controller
{

    // make api request to trigger led
    public function triggerLED(Request $request)
    {
        $url = 'http://localhost:3000/led/trigger';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'form_params' => [
                'led' => $request->led_number,
                'status' => $request->status
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'led triggered successfully',
            'data' => json_decode($response->getBody()->getContents())
        ]);

    }

    // make api request to get led status
    public function getLEDStatus(Request $request)
    {
        $url = 'http://localhost:3000/led/status';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'form_params' => [
                'led' => $request->led_number
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'led status fetched successfully',
            'data' => json_decode($response->getBody()->getContents())
        ]);
    }

    // install led
    public function installLED(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'shelf_number' => 'required',
            'led_unique_number' => 'required'
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse(
                'some fields are required', $validators->errors()
            );
        }

        \DB::beginTransaction();
        try {
            $led = Led::create([
                'shelf_number' => $request->shelf_number,
                'led_unique_number' => $request->led_unique_number
            ]);

            $url = 'http://localhost:3000/led/install';
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url, [
                'form_params' => [
                    'led' => $request->led_number
                ]
            ]);
            return ApiResponse::successResponse('Led Installed', [$response, $led], 201);
        } catch (\Exception $e) {
            return ApiResponse::errorResponse('something went wrong', $e->getMessage());
        }

    }


    // crud LEDs
    public function index()
    {
        $leds = Led::all();
        return ApiResponse::successResponse('leds retrieved', $leds, 200);
    }

    public function install(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'shelf_number' => 'required',
            'led_unique_number' => 'required'
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse(
                'some fields are required', $validators->errors(),
                400
            );
        }

        \DB::beginTransaction();
        try {
            $led = Led::create([
                'shelf_number' => $request->shelf_number,
                'led_unique_number' => $request->led_unique_number
            ]);

            \DB::commit();
            return ApiResponse::successResponse('Led Installed', $led, 201);

        } catch (\Exception $e) {
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $led = Led::find($id);
        if (!$led) {
            return ApiResponse::errorResponse('led not found', null, 404);
        }
        return ApiResponse::successResponse('led retrieved', $led, 200);
    }

    public function destroy($id)
    {
        $led = Led::find($id);
        if (!$led) {
            return ApiResponse::errorResponse('led not found', null, 404);
        }
        $led->delete();
        return ApiResponse::successResponse('led deleted', null, 200);
    }

}
