<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\BastController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\JabatanController;
use App\Http\Controllers\Api\V1\MonitoringController;
use App\Http\Controllers\Api\V1\PegawaiController;
use App\Http\Controllers\Api\V1\PenerimaanController;
use App\Http\Controllers\Api\V1\SatuanController;
use App\Http\Controllers\Api\V1\StokController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
});

Route::get('/category/select', [CategoryController::class, 'getAllForSelect'])->name('category.selectAll');
Route::apiResource('category', CategoryController::class);
Route::apiResource('satuan', SatuanController::class);
Route::apiResource('jabatan', JabatanController::class);
Route::get('penerimaan/history', [PenerimaanController::class, 'history']);
Route::patch('penerimaan/{id}/barang/{detailId}/layak', [PenerimaanController::class, 'markBarangLayak']);
Route::patch('penerimaan/{id}/confirm', [PenerimaanController::class, 'confirmPenerimaan']);
Route::apiResource('penerimaan', PenerimaanController::class);
Route::apiResource('account', AccountController::class)->except('create', 'store', 'delete');
Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
Route::get('/pegawai/select', [PegawaiController::class, 'getAllForSelect'])->name('pegawai.selectAll');
Route::get('/stok/select', [StokController::class, 'getAllForSelect'])->name('stok.selectAll');
Route::get('/bast/{id}', [BastController::class, 'downloadBast'])->name('bast.download');