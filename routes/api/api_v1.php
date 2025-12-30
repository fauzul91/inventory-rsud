<?php

use App\Http\Controllers\Api\V1\NotifikasiController;
use App\Http\Controllers\Api\V1\PemesananApprovalController;
use App\Http\Controllers\Api\V1\PemesananController;
use App\Http\Controllers\Api\V1\PenerimaanCheckController;
use App\Http\Controllers\Api\V1\PenerimaanHistoryController;
use App\Http\Controllers\Api\V1\PenerimaanWorkflowController;
use App\Http\Controllers\Api\V1\PengeluaranController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\Api\V1\BastController;
use App\Http\Controllers\Api\V1\StokController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\JabatanController;
use App\Http\Controllers\Api\V1\PegawaiController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\MonitoringController;
use App\Http\Controllers\Api\V1\PenerimaanController;
use App\Http\Controllers\Api\V1\PelaporanController;

Route::get('/sso/login', [SsoController::class, 'redirectToSso'])->name('sso.login');
Route::get('/sso/callback', [SsoController::class, 'handleCallback'])->name('sso.callback');

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/sso/logout', [SsoController::class, 'logout'])->name('sso.logout');
    Route::get('/category/select', [CategoryController::class, 'getAllForSelect'])->name('category.selectAll');
    Route::apiResource('category', CategoryController::class);
    Route::get('/jabatan/select', [JabatanController::class, 'getAllForSelect'])->name('jabatan.selectAll');
    Route::get('penerimaan/check', [PenerimaanCheckController::class, 'getAllCheckedPenerimaan']);
    Route::get('penerimaan/history', [PenerimaanHistoryController::class, 'history']);
    Route::get('penerimaan/checkHistory', [PenerimaanHistoryController::class, 'checkHistory']);
    Route::patch('penerimaan/{id}/barang/{detailId}/layak', [PenerimaanCheckController::class, 'updateKelayakanBarang']);
    Route::patch('penerimaan/{id}/confirm', [PenerimaanWorkflowController::class, 'confirmPenerimaan']);
    Route::apiResource('penerimaan', PenerimaanController::class);
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/pegawai/select', [PegawaiController::class, 'getAllForSelect'])->name('pegawai.selectAll');
    Route::get('/stok/{id}/bast-available', [PengeluaranController::class, 'getAvailableBastStokById']);
    Route::get('/stok/{id}/bast', [StokController::class, 'getDetailBastStockById'])->name('stok.detailBastStock');
    Route::get('/stok/select', [StokController::class, 'getAllForSelect'])->name('stok.selectAll');
    Route::get('/stok/year', [StokController::class, 'getAllYearForSelect'])->name('stok.year');
    Route::patch('penerimaan/{penerimaanId}/barang/{detailId}/paid', [PenerimaanWorkflowController::class, 'markDetailAsPaid']);
    Route::apiResource('stok', StokController::class)->except('create', 'destroy');
    Route::get('/bast/payment', [BastController::class, 'getAllPaymentBast'])->name('bast.paid');
    Route::get('/bast/unsigned', [BastController::class, 'getUnsignedBast'])->name('bast.unsigned');
    Route::get('/bast/signed', [BastController::class, 'getSignedBast'])->name('bast.signed');
    Route::get('/bast/unsigned/{id}/download', [BastController::class, 'downloadUnsignedBast'])->name('bast.unsigned.download');
    Route::get('/bast/signed/{id}/download', [BastController::class, 'downloadSignedBast'])->name('bast.signed.download');
    Route::post('/bast/upload/{id}', [BastController::class, 'upload'])->name('bast.upload');
    Route::get('/bast/history', [BastController::class, 'historyBast'])->name('bast.history');
    Route::get('/pegawai/profile', [PegawaiController::class, 'getPegawaiForProfile']);
    Route::apiResource('pegawai', PegawaiController::class);
    Route::patch('/pegawai/{id}/status', [PegawaiController::class, 'toggleStatus'])->name('pegawai.toggleStatus');
    Route::post('/pemesanan/{pemesananId}/alokasi-stok', [PengeluaranController::class, 'alokasiStokGudang']);
    Route::get('/pemesanan', [PemesananApprovalController::class, 'getAllPendingPemesanan']);
    Route::get('/pemesanan/riwayat-pj', [PemesananApprovalController::class, 'getAllPJRiwayatPemesanan']);
    Route::get('/pemesanan/approved-pj', [PemesananApprovalController::class, 'getAllPemesananApprovedPJ']);
    Route::get('/pemesanan/status', [PemesananController::class, 'getAllStatusPemesananInstalasi']);
    Route::get('/pemesanan/stok', [PemesananController::class, 'getAllStockPemesanan']);
    Route::patch('/pemesanan/{pemesananId}/quantity-pj', [PemesananApprovalController::class, 'updateQuantityPenanggungJawab']);
    Route::apiResource('pemesanan', PemesananController::class)->except('index', 'update', 'destroy');
    Route::get('/pengeluaran', [PengeluaranController::class, 'index']);
    Route::get('/pengeluaran/export/excel', [PengeluaranController::class, 'exportExcel']);
    Route::get('/pelaporan/dashboard', [PelaporanController::class, 'index']);
    Route::get('/pelaporan/penerimaan-per-bulan', [PelaporanController::class, 'penerimaanPerBulan']);
    Route::get('pelaporan/pengeluaran-per-bulan', [PelaporanController::class, 'pengeluaranPerBulan']);
    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::patch('/notifikasi/{id}/markRead', [NotifikasiController::class, 'markAsRead']);
    Route::patch('/notifikasi/markAll', [NotifikasiController::class, 'markAllAsRead']);
    Route::delete('/notifikasi/delete-all', [NotifikasiController::class, 'destroyAll']);
    Route::delete('/notifikasi/{id}', [NotifikasiController::class, 'destroy']);
    Route::get('/me', function (Request $request) {
        $user = $request->user();
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
        ];
    });
});

