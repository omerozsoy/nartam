<?php

use App\Http\Controllers\IlanController;
use App\Http\Controllers\KimlikController;
use App\Http\Controllers\TeklifController;
use App\Http\Controllers\YonetimController;
use Illuminate\Support\Facades\Route;

// Ana sayfa + canlı güncelleme API'si
Route::get('/', [IlanController::class, 'index'])->name('ilanlar.liste');
Route::get('/api/ilanlar', [IlanController::class, 'api'])->name('ilanlar.api');

// Kimlik (yalnızca misafirler görebilir)
Route::middleware('guest')->group(function () {
    Route::get('/giris', [KimlikController::class, 'girisFormu'])->name('giris');
    Route::post('/giris', [KimlikController::class, 'girisYap']);
    Route::get('/kayit', [KimlikController::class, 'kayitFormu'])->name('kayit');
    Route::post('/kayit', [KimlikController::class, 'kayitOl']);
});

Route::post('/cikis', [KimlikController::class, 'cikis'])->name('cikis')->middleware('auth');

// Teklif verme (giriş zorunlu)
Route::post('/teklif', [TeklifController::class, 'store'])->name('teklif')->middleware('auth');

// Yönetim paneli (yalnızca yönetici)
Route::middleware(['auth', 'yonetici'])->prefix('yonetim')->group(function () {
    Route::get('/', [YonetimController::class, 'index'])->name('yonetim');
    Route::post('/ilan', [YonetimController::class, 'ilanOlustur'])->name('yonetim.ilan');
});
