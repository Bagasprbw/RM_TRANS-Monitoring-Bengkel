<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Authentikasi\AuthController;
use App\Http\Controllers\Api\Armada\ArmadaController;
use App\Http\Controllers\Api\Armada\JenisArmadaController;
use App\Http\Controllers\Api\KategoriKomponen\KategoriKomponenController;

// Login
Route::post('/login', [AuthController::class, 'login']);

// midleware auth
Route::middleware('auth:sanctum')->group(function () {
    // Armada
    Route::apiResource('armada', ArmadaController::class);
    // Jenis Armada
    Route::apiResource('jenis_armada', JenisArmadaController::class);
    // Categori Komponen (hanya Get all, create, delete)
    Route::get('kategori_komponen', [KategoriKomponenController::class, 'index']);
    Route::post('kategori_komponen', [KategoriKomponenController::class, 'store']);
    Route::delete('kategori_komponen/{id}', [KategoriKomponenController::class, 'destroy']);

    // Monitoring Armada Aktif
    Route::get('monitoring_armada_aktif/reminders', [\App\Http\Controllers\Api\MonitoringAktif\MonitoringArmadaAktifController::class, 'reminders']);
    Route::get('monitoring_armada_aktif/available', [\App\Http\Controllers\Api\MonitoringAktif\MonitoringArmadaAktifController::class, 'availableArmada']);
    Route::put('monitoring_armada_aktif/{monitoring}', [\App\Http\Controllers\Api\MonitoringAktif\MonitoringArmadaAktifController::class, 'update']);
    Route::apiResource('monitoring_armada_aktif', \App\Http\Controllers\Api\MonitoringAktif\MonitoringArmadaAktifController::class)->except(['create', 'edit', 'update']);

    // Log Kilometer
    Route::apiResource('log_kilometer', \App\Http\Controllers\Api\LogKilometer\LogKilometerController::class)->only(['index', 'store']);

    // Komponen Armada
    Route::get('monitoring_armada_aktif/{monitoring_id}/komponen', [\App\Http\Controllers\Api\KomponenArmada\KomponenArmadaController::class, 'index']);
    Route::post('monitoring_armada_aktif/{monitoring_id}/komponen', [\App\Http\Controllers\Api\KomponenArmada\KomponenArmadaController::class, 'store']);
    Route::post('komponen_armada/{id}/reset', [\App\Http\Controllers\Api\KomponenArmada\KomponenArmadaController::class, 'reset']);
    Route::delete('komponen_armada/{id}', [\App\Http\Controllers\Api\KomponenArmada\KomponenArmadaController::class, 'destroy']);
    Route::get('riwayat_perawatan', [\App\Http\Controllers\Api\KomponenArmada\RiwayatPerawatanController::class, 'index']);

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);
});
