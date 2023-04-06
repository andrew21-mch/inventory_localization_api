<?php

use App\Http\Controllers\Api\OutOfStockController;
use App\Http\Controllers\Api\StatisticsController;
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
        Route::post('/', [RestockController::class, 'store']);
        Route::put('/{id}', [RestockController::class, 'update']);
        Route::delete('/{id}', [RestockController::class, 'destroy']);
    });

    Route::group(['prefix' => 'sales'], function () {
        Route::get('/', [SaleController::class, 'index']);
        Route::get('/{id}', [SaleController::class, 'show']);
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
    });


    Route::prefix('statistics')->group(function () {
        Route::get('/', [StatisticsController::class, 'statistics']);
        Route::get('/sales', [StatisticsController::class, 'sales_statistics']);
    });

});
