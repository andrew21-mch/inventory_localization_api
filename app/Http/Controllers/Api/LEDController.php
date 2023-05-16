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
        $url = 'http://192.168.0.168/api';

        $led = Led::find($request->led_id);
        if (!$led) {
            return response()->json([
                'success' => false,
                'message' => 'LED not found',
            ]);
        }


        // Create the JSON payload
        $payload = [
            'action' => $request->action,
            'pinNumber' => $request->pinNumber,
        ];

        // Use curl to make the API request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the HTTP status code
        curl_close($ch);



        if ($httpCode === 200) {
            $led->status = $request->action;
            $led->save();
            return response()->json([
                'success' => true,
                'message' => 'LED triggered successfully',
            ]);
        } else {
            // Failed response
            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger LED',
                'http_code' => $httpCode
            ]);
        }
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

    public function search(Request $request)
    {
        $leds = Led::where('shelf_number', 'LIKE', '%' . $request->search . '%')
            ->orWhere('led_unique_number', 'LIKE', '%' . $request->search . '%')
            ->get();
        return ApiResponse::successResponse('leds searched', $leds, 200);
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

            $url = 'http://localhost:3000/led/install';
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url, [
                'form_params' => [
                    'led' => $request->led_number
                ]
            ]);

            $data = json_decode($response->getBody()->getContents());
            $allResponse = [$data, $led];

            \DB::commit();
            return ApiResponse::successResponse('Led Installed', $allResponse, 201);

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

        // test led
        $url = 'http://localhost:3000/led/test';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'form_params' => [
                'led' => $led->led_unique_number
            ]
        ]);

        $data = json_decode($response->getBody()->getContents());
        $allResponse = [$data, $led];

        return ApiResponse::successResponse('led retrieved', $allResponse, 200);
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
