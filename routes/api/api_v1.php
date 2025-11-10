<?php

use App\Http\Controllers\Api\V1\CategoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('category', CategoryController::class);
Route::apiResource('satuan', SatuanController::class);
Route::apiResource('jabatan', JabatanController::class);