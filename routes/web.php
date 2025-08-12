<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group
| which contains the "web" middleware group. Now create something great!
|
*/

// Rota para o Dashboard (página inicial)
Route::get('/', function () {
    return view('app'); // Aponta para o seu novo layout principal
});

// Rota para Clientes
Route::get('/clients', function () {
    return view('app'); // Aponta para o seu novo layout principal
});

// NOVA Rota para Estoque
Route::get('/stock', function () {
    return view('app'); // Aponta para o seu novo layout principal
});

Route::get('/championship', function () {
    return view('app');
});