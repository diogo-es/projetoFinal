<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});

use App\Http\Controllers\TokenController;

Route::post('/set-token', [TokenController::class, 'setToken'])->name('set-token');
Route::post('/clear-token', [TokenController::class, 'clearToken'])->name('clear-token');

Route::get('/instrucoes', function () {
    return view('instrucoes');
})->name('instrucoes');


