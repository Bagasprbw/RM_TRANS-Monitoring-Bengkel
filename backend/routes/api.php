<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Authentikasi\AuthController;
use App\Http\Controllers\Api\Armada\ArmadaController;
use App\Http\Controllers\Api\Armada\JenisArmadaController;
use App\Http\Controllers\Api\Armada\MerkArmadaController;
use App\Http\Controllers\Api\KategoriKomponen\KategoriKomponenController;
use App\Http\Controllers\Api\MonitoringAktif\MonitoringArmadaAktifController;
use App\Http\Controllers\Api\KomponenArmada\KomponenArmadaController;
use App\Http\Controllers\Api\KomponenArmada\RiwayatPerawatanController;
use App\Http\Controllers\Api\LogKilometer\LogKilometerController;



// Login
Route::post('/login', [AuthController::class, 'login']);

// midleware auth
Route::middleware('auth:sanctum')->group(function () {
    // Armada
    Route::apiResource('armada', ArmadaController::class);
    // Jenis Armada
    Route::apiResource('jenis_armada', JenisArmadaController::class);
    // Merk Armada
    Route::apiResource('merk_armada', MerkArmadaController::class);
    // Categori Komponen (hanya Get all, create, delete)
    Route::get('kategori_komponen', [KategoriKomponenController::class, 'index']);
    Route::post('kategori_komponen', [KategoriKomponenController::class, 'store']);
    Route::delete('kategori_komponen/{id}', [KategoriKomponenController::class, 'destroy']);

    // Monitoring Armada Aktif
    Route::get('monitoring_armada_aktif/reminders', [MonitoringArmadaAktifController::class, 'reminders']);
    Route::get('monitoring_armada_aktif/available', [MonitoringArmadaAktifController::class, 'availableArmada']);
    Route::put('monitoring_armada_aktif/{monitoring}', [MonitoringArmadaAktifController::class, 'update']);
    Route::apiResource('monitoring_armada_aktif', MonitoringArmadaAktifController::class)->except(['create', 'edit', 'update']);

    // Log Kilometer
    Route::apiResource('log_kilometer', LogKilometerController::class)->only(['index', 'store']);

    // Komponen Armada
    Route::get('monitoring_armada_aktif/{monitoring_id}/komponen', [KomponenArmadaController::class, 'index']);
    Route::post('monitoring_armada_aktif/{monitoring_id}/komponen', [KomponenArmadaController::class, 'store']);
    Route::post('komponen_armada/{id}/reset', [KomponenArmadaController::class, 'reset']);
    Route::put('komponen_armada/{id}', [KomponenArmadaController::class, 'update']);
    Route::delete('komponen_armada/{id}', [KomponenArmadaController::class, 'destroy']);
    Route::get('riwayat_perawatan', [RiwayatPerawatanController::class, 'index']);

    // Profiling
    Route::post('update-profile', [AuthController::class, 'updateProfile']);

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);
});
