<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [LoginController::class, 'login'])->name('api.login');
Route::post('/logout', [LoginController::class, 'logout'])->name('api.logout')->middleware('auth:sanctum');

Route::group(['prefix' => 'transactions', 'middleware' => ['auth:sanctum']], function () {
    Route::post('/', [TransactionController::class, 'store'])->name('transaction.store');
    Route::get('/', [TransactionController::class, 'index'])->name('transaction.index');
});
