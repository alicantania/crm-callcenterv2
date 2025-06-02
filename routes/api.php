<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\UserController;

Route::get('/companies', [CompanyController::class, 'index']);
Route::get('/companies/{company}/calls', [CompanyController::class, 'calls']);
Route::get('/api/users', [UserController::class, 'index']);
