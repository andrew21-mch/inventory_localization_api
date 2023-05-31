<?php

use App\Http\Controllers\Api\LEDController;
use App\Http\Controllers\Api\OutOfStockController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\RestockController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ComponentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
/* Api description */

Route::get('/', function () {

    $data = [
        'message' => 'Welcome to the API',
        'endpoints' => [
            'auth' => [
                'login' => '/api/login',
                'register' => '/api/register',
                'logout' => '/api/auth/logout',
                'user' => '/api/auth/user',
            ],
            'components' => [
                'index' => '/api/components',
                'show' => '/api/components/{id}',
                'store' => '/api/components',
                'update' => '/api/components/{id}',
                'destroy' => '/api/components/{id}',
            ],
            'restocks' => [
                'index' => '/api/restocks',
                'show' => '/api/restocks/{id}',
                'store' => '/api/restocks',
                'update' => '/api/restocks/{id}',
                'destroy' => '/api/restocks/{id}',
            ],
            'sales' => [
                'index' => '/api/sales',
                'show' => '/api/sales/{id}',
                'store' => '/api/sales',
                'update' => '/api/sales/{id}',
                'destroy' => '/api/sales/{id}',
            ],
            'suppliers' => [
                'index' => '/api/suppliers',
                'show' => '/api/suppliers/{id}',
                'store' => '/api/suppliers',
                'update' => '/api/suppliers/{id}',
                'destroy' => '/api/suppliers/{id}',
            ],
        ],
        'base_url' => 'http://192.168.0.169/api'
    ];

    return view('docs', ['data' => $data]);
});



Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('verify-code', [AuthController::class, 'verifyCode']);

Route::group(['prefix' => 'auth', 'middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::group(['prefix' => 'components'], function () {
        Route::get('/', [ComponentController::class, 'index']);
        Route::get('/{id}', [ComponentController::class, 'show']);
        Route::get('/search/component', [ComponentController::class, 'search']);
        Route::post('/', [ComponentController::class, 'store']);
        Route::put('/{id}', [ComponentController::class, 'update']);
        Route::delete('/{id}', [ComponentController::class, 'destroy']);
    });

    Route::group(['prefix' => 'restocks'], function () {
        Route::get('/', [RestockController::class, 'index']);
        Route::get('/{id}', [RestockController::class, 'show']);
        Route::get('/search/restocks', [RestockController::class, 'search']);
        Route::post('/', [RestockController::class, 'store']);
        Route::put('/{id}', [RestockController::class, 'update']);
        Route::delete('/{id}', [RestockController::class, 'destroy']);
    });

    Route::group(['prefix' => 'sales'], function () {
        Route::get('/', [SaleController::class, 'index']);
        Route::get('/{id}', [SaleController::class, 'show']);
        Route::get('/search/sales', [SaleController::class, 'search']);
        Route::get('/search/sales_by_date', [SaleController::class, 'filterSales']);
        Route::post('/', [SaleController::class, 'store']);
        Route::put('/{id}', [SaleController::class, 'update']);
        Route::delete('/{id}', [SaleController::class, 'destroy']);
    });

    Route::group(['prefix' => 'suppliers'], function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::get('/{id}', [SupplierController::class, 'show']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::put('/{id}', [SupplierController::class, 'update']);
        Route::delete('/{id}', [SupplierController::class, 'destroy']);
    });

    Route::prefix('out_of_stocks')->group(function () {
        Route::get('/', [OutOfStockController::class, 'index']);
        Route::get('/search/out_of_stocks', [OutOfStockController::class, 'search']);
    });


    Route::prefix('statistics')->group(function () {
        Route::get('/', [StatisticsController::class, 'statistics']);
        Route::get('/sales', [StatisticsController::class, 'sales_statistics']);
        Route::get('/restocks', [StatisticsController::class, 'restocks_statistics']);
        Route::get('/restocks_statistics_by_date', [StatisticsController::class, 'restocks_statistics_by_date']);
        Route::get('/sales_statistics_by_date', [StatisticsController::class, 'sales_statistics_by_date']);
    });

    // leds
    Route::prefix('leds')->group(function () {
        Route::get('/', [LEDController::class, 'index']);
        Route::get('/microcontrollers/get', [LEDController::class, 'loadMicroncontrollers']);
        Route::get('/pins/load/{mciId}', [LEDController::class, 'loadPins']);
        Route::get('/{id}/test', [LEDController::class, 'test']);
        Route::post('/', [LEDController::class, 'store']);
        Route::post('/trigger', [LEDController::class, 'triggerLED']);
        Route::get('/search/leds', [LEDController::class, 'search']);
        Route::post('/', [LEDController::class, 'install']);
        Route::put('/{id}', [LEDController::class, 'update']);
        Route::delete('/{id}', [LEDController::class, 'destroy']);
    });

    // users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/profile', [UserController::class, 'profile']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::put('/auth/update-password', [UserController::class, 'updatePassword']);
        Route::delete('/{id}', [UserController::class, 'destroy']);


    });


});
Route::get('expenses', [StatisticsController::class, 'calculateExpenditure']);


