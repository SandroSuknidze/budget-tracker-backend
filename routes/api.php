<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('account', [AccountController::class, 'create']);
    Route::get('accounts', [AccountController::class, 'index']);
    Route::get('account', [AccountController::class, 'show']);
    Route::put('account/{id}', [AccountController::class, 'update']);
    Route::delete('account/{id}', [AccountController::class, 'delete']);


    Route::get('currencies', [ExcelController::class, 'index']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'create']);
    Route::delete('categories/{id}', [CategoryController::class, 'delete']);
    Route::get('categories/{categoryId}/transactions', [CategoryController::class, 'transactionsCategoryCheck']);

    Route::post('transactions', [TransactionController::class, 'create']);
    Route::get('transactions', [TransactionController::class, 'index']);
});



