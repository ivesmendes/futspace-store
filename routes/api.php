<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\StockItemController;
use App\Http\Controllers\API\ChampionShipSaleController;
use App\Http\Controllers\Api\ShirtCounterController;
use App\Http\Controllers\API\LossController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Essas rotas são carregadas pelo RouteServiceProvider dentro de um
| grupo que tem prefixo "/api" e middleware "api".
|
*/

Route::get('customers', [CustomerController::class, 'index']);
Route::post('customers', [CustomerController::class, 'store']);
Route::put   ('customers/{customer}', [CustomerController::class, 'update']);
Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);

Route::get('stock-items', [StockItemController::class, 'index']);
Route::post('stock-items', [StockItemController::class, 'store']);
Route::delete('stock-items/{stockItem}', [StockItemController::class, 'destroy']);
Route::get('stock-value', [StockItemController::class, 'getStockValue']); 

Route::get('/championship-sales', [ChampionShipSaleController::class, 'index']);
Route::post('/championship-sales', [ChampionShipSaleController::class, 'store']);

Route::get('/shirt-counter', [ShirtCounterController::class, 'getCount']);
Route::post('/shirt-counter', [ShirtCounterController::class, 'updateCount']);

Route::get('losses',        [LossController::class, 'index']);
Route::post('losses',       [LossController::class, 'store']);
Route::put('losses/{loss}', [LossController::class, 'update']);
Route::delete('losses/{loss}', [LossController::class, 'destroy']);

Route::get('losses-total', [LossController::class, 'total']); // soma para o dashboard