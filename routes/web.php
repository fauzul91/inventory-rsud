<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Get localhost:8001/auth => redirect ke localhost:8000/..............
Route::get('/auth/', [AuthController::class, 'index'])->name('auth.index');
Route::get('/auth/callback', [AuthController::class, 'ssoCallback'])->name('auth.sso'); 

// Test BAST PDF
Route::get('/test-bast/{id}', function ($id) {
    $penerimaan = \App\Models\Penerimaan::with(['detailBarang', 'detailPegawai.pegawai'])
        ->findOrFail($id);

    return view('pdf.bast', compact('penerimaan'));
});