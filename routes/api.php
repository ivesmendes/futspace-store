<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\StockItemController;
use App\Http\Controllers\API\ChampionShipSaleController;

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
Route::get('stock-value', [StockItemController::class, 'getStockValue']); // <--- NOVA ROTA

Route::get('/championship-sales', [ChampionShipSaleController::class, 'index']);
Route::post('/championship-sales', [ChampionShipSaleController::class, 'store']);