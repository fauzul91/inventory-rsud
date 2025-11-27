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
Route::get('/stok', [StokController::class, 'getAllStoks'])->name('stok.index');
Route::get('/stok/paid', [StokController::class, 'getPaidBastStock'])->name('bast.paid');
Route::get('/stok/unpaid', [StokController::class, 'getUnpaidBastStock'])->name('bast.unpaid');
Route::get('/bast/unsigned', [BastController::class, 'getUnsignedBast'])->name('bast.unsigned');
Route::get('/bast/signed', [BastController::class, 'getSignedBast'])->name('bast.signed');
Route::get('/bast/unsigned/{id}/download', [BastController::class, 'downloadUnsignedBast'])->name('bast.unsigned.download');
Route::get('/bast/signed/{id}/download', [BastController::class, 'downloadSignedBast'])->name('bast.signed.download');
Route::post('/bast/upload/{id}', [BastController::class, 'upload'])->name('bast.upload');
Route::get('/bast/history', [BastController::class, 'historyBast'])->name('bast.history');
Route::apiResource('pegawai', PegawaiController::class);
Route::patch('/pegawai/{id}/status', [PegawaiController::class, 'toggleStatus'])->name('pegawai.toggleStatus');
