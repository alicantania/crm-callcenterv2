<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\SaleController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::apiResource('companies', CompanyController::class);
    Route::get('companies/{company}/calls', [CompanyController::class, 'calls']);
    
    Route::apiResource('sales', SaleController::class);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
