<?php

use App\Http\Controllers\API\EmployeesController;
use App\Http\Controllers\API\GoodsController;
use App\Http\Controllers\API\OrdersController;
use App\Http\Controllers\API\PlantingsController;
use App\Http\Controllers\API\StockController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/user', [UserController::class, 'authorizeUser']);

Route::post('/employees',[EmployeesController::class, 'getEmployees']);
Route::post('/stock',[StockController::class, 'getPositions']);
Route::post('/plantings',[PlantingsController::class, 'getPlantings']);
Route::post('/orders',[OrdersController::class, 'getOrders']);
Route::post('/goods',[GoodsController::class, 'getGoods']);

Route::post('/new_employee',[EmployeesController::class, 'setEmployee']);
Route::post('/new_in_stock',[StockController::class, 'setPosition']);
Route::post('/new_planting',[PlantingsController::class, 'setPlanting']);
Route::post('/new_order',[OrdersController::class, 'setOrder']);
Route::post('/new_letter',[OrdersController::class, 'setLetter']);
Route::post('/new_reason',[OrdersController::class, 'setReason']);
Route::post('/new_good',[GoodsController::class, 'setGood']);
