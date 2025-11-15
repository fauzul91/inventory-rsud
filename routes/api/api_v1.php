<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\JabatanController;
use App\Http\Controllers\Api\V1\MonitoringController;
use App\Http\Controllers\Api\V1\PenerimaanController;
use App\Http\Controllers\Api\V1\SatuanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
});

Route::get('/selectCategory', [CategoryController::class, 'getAllForSelect'])->name('category.selectAll');
Route::apiResource('category', CategoryController::class);
Route::apiResource('satuan', SatuanController::class);
Route::apiResource('jabatan', JabatanController::class);
Route::apiResource('penerimaan', PenerimaanController::class);
Route::patch('/detail/{detailId}/layak', [PenerimaanController::class, 'markBarangLayak']);
Route::patch('/{id}/confirm', [PenerimaanController::class, 'confirmPenerimaan']);
Route::apiResource('account', AccountController::class)->except('create', 'store', 'delete');
Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');