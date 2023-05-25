<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client as SmsClient;


class AuthController extends Controller
{
    use ApiResponse;
    public function login(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|string',
        ]);

        if ($validators->fails()) {
           ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return ApiResponse::errorResponse('sorry, we can\'t find this user', null, 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return ApiResponse::errorResponse('sorry, password is incorrect', null, 401);
        }

        $tokenResult = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'login successful',
            'user' => $user,
            'access_token' => $tokenResult
        ]);
    }

    public function register(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        try{
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password)
            ]);

            $user->save();

            return ApiResponse::successResponse('user created successfully', $user, 201);
        }catch(\Exception $e){
            return ApiResponse::errorResponse('something went wrong', $e->getMessage(), 500);
        }
    }
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return ApiResponse::successResponse('logged out successfully', null, 200);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }



    /*
    Unused methods
    */
    public function resetPassword(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'phone' => 'required',
        ]);


        if ($validators->fails()) {
            return ApiResponse::errorResponse('some fields are not valid', $validators->errors(), 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return ApiResponse::errorResponse('sorry, we can\'t find this user', null, 401);
        }

        // use Twilio to send sms to user with code
        $code = rand(1000, 9999);
        $user->update([
            'reset_code' => $code,
            'reset_code_expires_at' => Carbon::now()->addMinutes(5)
        ]);

        $this->sendSMS($user->phone, $user->name, $code, 'smartShop');
    }


    public function sendSMS($phone, $name, $code = null, $sender)
    {
        $account_sid = env('TWILIO_ACCOUNT_SID');
        $auth_token = env('TWILIO_AUTH_TOKEN');
        $twilio_number = env('TWILIO_NUMBER');

        $client = new SmsClient($account_sid, $auth_token);
        $client->messages->create(
            "+237681610898",
            [
                'from' => $twilio_number,
                'body' => "Hello $name, Someone Request a password reset code for your account on smartShop, here is your code <b>$code</b>"
            ]
        );

        return True;
    }

    public function sendResetCodeViaEmail($email, $name, $code = null, $sender)
    {
        $data = [
            'name' => $name,
            'code' => $code,
            'sender' => $sender
        ];

        Mail::send('emails.reset-password' , $data, function($message) use ($email, $name, $code, $sender){
            $message->to($email, $name)->subject('Reset Password');
            $message->from($sender, 'SmartShop');
        });

        return True;
    }

    /*
    End Unused methods
    */
}
