<?php

namespace App\Http\Controllers\Api\ApiResponse;

use Illuminate\Http\Request;

trait ApiResponse
{
    public static function errorResponse($message, $errors=null, $code=null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors

        ], $code);
    }

    public static function successResponse($message, $data=null, $code=null)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
}
