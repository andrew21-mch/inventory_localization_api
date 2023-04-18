<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Led;
use Illuminate\Http\Request;

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
        $url = 'http://localhost:3000/led/install';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'form_params' => [
                'led' => $request->led_number
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'led installed successfully',
            'data' => json_decode($response->getBody()->getContents())
        ]);
    }


    // crud LEDs
    public function index(){
        $leds = Led::all();
    }

}
