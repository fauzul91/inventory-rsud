<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\JabatanController;
use App\Http\Controllers\Api\V1\PenerimaanController;
use App\Http\Controllers\Api\V1\SatuanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('category', CategoryController::class);
Route::apiResource('satuan', SatuanController::class);
Route::apiResource('jabatan', JabatanController::class);
Route::apiResource('penerimaan', PenerimaanController::class);
Route::patch('/detail/{detailId}/layak', [PenerimaanController::class, 'setLayak']);
Route::patch('/{id}/confirm', [PenerimaanController::class, 'confirm']);