<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Led;
use App\Models\MCU;
use App\Models\Microcontroller;
use App\Models\Pin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LEDController extends Controller
{

    // make api request to trigger led
    public static function triggerLED(Request $request)
    {
        // Validate the request
        $validators = Validator::make($request->all(), [
            'microcontroller_id' => 'required|integer',
            'led_id' => 'required|integer',
            'pinNumber' => 'required|integer',
        ]);

        if ($validators->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'some fields are not valid',
                'errors' => $validators->errors(),
            ]);
        }

        // Get the MCU
        $mcu = Microcontroller::find($request->microcontroller_id);
        if (!$mcu) {
            return response()->json([
                'success' => false,
                'message' => 'MCU not found',
            ]);
        }

        // Get the MCU IP address
        $mcu_ip = $mcu->ip_address;
        if (!$mcu_ip) {
            return response()->json([
                'success' => false,
                'message' => 'MCU IP address not found',
            ]);
        }

        // Build the API URL
        $url = 'http://' . $mcu_ip . '/api/';

        $led = Led::find($request->led_id);
        if (!$led) {
            return response()->json([
                'success' => false,
                'message' => 'LED not found',
            ]);
        }

        // Get the pin
        $pin = Pin::where('pinNumber', $request->pinNumber)->where('microcontroller_id', $request->microcontroller_id)->first();

        if (!$pin) {
            return response()->json([
                'success' => false,
                'message' => 'Pin not found',
            ]);
        }


        // Create the JSON payload
        $payload = [
            'action' => $request->action,
            'pinNumber' => $pin->pinNumber,
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


    public static function testLed($id)
    {

        $led = Led::find($id);
        if (!$led) {
            return response()->json([
                'success' => false,
                'message' => 'LED not found',
            ]);
        }

        // Get the MCU
        $mcu = Microcontroller::find($led->microcontroller_id);
        if (!$mcu) {
            return response()->json([
                'success' => false,
                'message' => 'MCU not found',
            ]);
        }

        // Get the MCU IP address
        $mcu_ip = $mcu->ip_address;
        if (!$mcu_ip) {
            return response()->json([
                'success' => false,
                'message' => 'MCU IP address not found',
            ]);
        }

        // Build the API URL
        $url = 'http://' . $mcu_ip . '/api/';


        // Create the JSON payload
        $payload = [
            'action' => 'on',
            'pinNumber' => $led->led_unique_number,
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
            // Success
            return true;
        } else {
            return false;
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


    // crud LEDs
    public function index()
    {
        $leds = Led::with('microcontroller', 'pin')->get();
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
            'pin_id' => 'required',
            'microcontroller_id'
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse(
                'some fields are required', $validators->errors()
            );
        }

        $pin = Pin::find($request->pin_id);
        if (!$pin) {
            return ApiResponse::errorResponse('pin not found', null);
        }

        $mcu = Microcontroller::find($request->microcontroller_id);
        if (!$mcu) {
            return ApiResponse::errorResponse('microcontroller not found', null);
        }

        $url = 'http://' . $mcu->ip_address . '/api/';

        \DB::beginTransaction();
        try {
            $led = Led::create([
                'shelf_number' => $request->shelf_number,
                'pin_id' => $request->pin_id,
                'microcontroller_id' => $request->microcontroller_id
            ]);

            $payload = [
                'action' => 'on',
                'pinNumber' => $pin->pinNumber,
            ];

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
                // Success
                $led->status = 'on';
                $led->save();
            } else {
                return ApiResponse::errorResponse('something went wrong', null, 500);
            }

            $allResponse = Led::with('microcontroller', 'pin')->get();

            \DB::commit();
            return ApiResponse::successResponse('Led Installed', $allResponse, 201);

        } catch (\Exception $e) {
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
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


    // pins

    public function loadPins($mcuId)
    {
        $pins = Pin::where('microcontroller_id', $mcuId)->first();
        if (!$pins) {
            return ApiResponse::errorResponse('pins not found', null, 404);
        }
        return ApiResponse::successResponse('pins retrieved', $pins, 200);
    }

    // load microncontroller
    public function loadMicroncontrollers()
    {
        $mcu = Microcontroller::all();
        return ApiResponse::successResponse('microncontroller retrieved', $mcu, 200);
    }
}
