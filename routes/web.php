<?php

use App\Http\Controllers\AdresController;
use App\Http\Controllers\HesapController;
use App\Http\Controllers\IlanController;
use App\Http\Controllers\KimlikController;
use App\Http\Controllers\OdemeController;
use App\Http\Controllers\SayfaController;
use App\Http\Controllers\TeklifController;
use App\Http\Controllers\YonetimController;
use Illuminate\Support\Facades\Route;

// Ana sayfa + canlı güncelleme API'si
Route::get('/', [IlanController::class, 'index'])->name('ilanlar.liste');
Route::get('/muzayedeler', [IlanController::class, 'muzayedeler'])->name('muzayedeler');
Route::get('/muzayede/{muzayede}', [IlanController::class, 'muzayedeGoster'])->name('muzayede.goster');
Route::get('/api/ilanlar', [IlanController::class, 'api'])->name('ilanlar.api');
Route::get('/api/ara', [IlanController::class, 'ara'])->name('ilanlar.ara');
Route::get('/ilan/{ilan}', [IlanController::class, 'goster'])->name('ilan.goster');

// Statik içerik sayfaları
Route::get('/iletisim', [SayfaController::class, 'iletisim'])->name('iletisim');
Route::get('/ekspertiz', [SayfaController::class, 'ekspertiz'])->name('ekspertiz');

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
Route::post('/takip/{ilan}', [App\Http\Controllers\TakipController::class, 'toggle'])->name('takip')->middleware('auth');

// Üye paneli — pey verilen eserler
Route::middleware('auth')->group(function () {
    Route::get('/hesabim', [HesapController::class, 'index'])->name('hesabim');
    Route::get('/api/hesabim', [HesapController::class, 'api'])->name('hesabim.api');
    Route::get('/odeme/{ilan}', [OdemeController::class, 'goster'])->name('odeme');

    // Adreslerim
    Route::get('/adreslerim', [AdresController::class, 'index'])->name('adresler');
    Route::post('/adreslerim', [AdresController::class, 'store'])->name('adresler.ekle');
    Route::post('/adreslerim/{adres}/sil', [AdresController::class, 'destroy'])->name('adresler.sil');
});

// Yönetim paneli (yalnızca yönetici)
Route::middleware(['auth', 'yonetici'])->prefix('yonetim')->group(function () {
    Route::get('/', [YonetimController::class, 'index'])->name('yonetim');
    Route::get('/eserler', [YonetimController::class, 'eserler'])->name('yonetim.eserler');
    Route::post('/eserler/sil-hepsi', [YonetimController::class, 'tumEserleriSil'])->name('yonetim.eserler.sil-hepsi');
    Route::get('/slider', [YonetimController::class, 'slider'])->name('yonetim.slider');
    Route::post('/slider', [YonetimController::class, 'sliderKaydet'])->name('yonetim.slider.kaydet');
    Route::get('/carusel', [YonetimController::class, 'carusel'])->name('yonetim.carusel');
    Route::post('/carusel', [YonetimController::class, 'caruselKaydet'])->name('yonetim.carusel.kaydet');
    Route::get('/kartlar', [YonetimController::class, 'kartlar'])->name('yonetim.kartlar');
    Route::post('/kartlar', [YonetimController::class, 'kartlarKaydet'])->name('yonetim.kartlar.kaydet');
    Route::get('/muzayedeler', [YonetimController::class, 'muzayedeler'])->name('yonetim.muzayedeler');
    Route::get('/muzayede/yeni', [YonetimController::class, 'muzayedeYeni'])->name('yonetim.muzayede.yeni');
    Route::post('/muzayede', [YonetimController::class, 'muzayedeOlustur'])->name('yonetim.muzayede.olustur');
    Route::get('/muzayede/{muzayede}/duzenle', [YonetimController::class, 'muzayedeDuzenle'])->name('yonetim.muzayede.duzenle');
    Route::post('/muzayede/{muzayede}/guncelle', [YonetimController::class, 'muzayedeGuncelle'])->name('yonetim.muzayede.guncelle');
    Route::post('/muzayede/{muzayede}/aktif', [YonetimController::class, 'muzayedeAktif'])->name('yonetim.muzayede.aktif');
    Route::post('/muzayede/{muzayede}/program', [YonetimController::class, 'muzayedeProgram'])->name('yonetim.muzayede.program');
    Route::post('/muzayede/{muzayede}/sil', [YonetimController::class, 'muzayedeSil'])->name('yonetim.muzayede.sil');
    Route::get('/eser/yeni', [YonetimController::class, 'eserYeni'])->name('yonetim.eser.yeni');
    Route::get('/toplu', [YonetimController::class, 'toplu'])->name('yonetim.toplu');
    Route::post('/toplu/onizle', [YonetimController::class, 'topluOnizle'])->name('yonetim.toplu.onizle');
    Route::post('/toplu/onayla', [YonetimController::class, 'topluOnayla'])->name('yonetim.toplu.onayla');
    Route::post('/toplu', [YonetimController::class, 'topluKaydet'])->name('yonetim.toplu.kaydet');
    Route::post('/toplu/geri-al', [YonetimController::class, 'topluGeriAl'])->name('yonetim.toplu.gerial');
    Route::post('/ilan', [YonetimController::class, 'ilanOlustur'])->name('yonetim.ilan');
    Route::get('/ilan/{ilan}/duzenle', [YonetimController::class, 'ilanDuzenle'])->name('yonetim.ilan.duzenle');
    Route::post('/ilan/{ilan}/guncelle', [YonetimController::class, 'ilanGuncelle'])->name('yonetim.ilan.guncelle');
    Route::post('/ilan/{ilan}/sil', [YonetimController::class, 'ilanSil'])->name('yonetim.ilan.sil');
    Route::get('/pey-adimlari', [YonetimController::class, 'peyAdimlari'])->name('yonetim.pey');
    Route::post('/pey-adimlari', [YonetimController::class, 'peyAdimiEkle'])->name('yonetim.pey.ekle');
    Route::post('/pey-adimlari/{peyAdimi}/guncelle', [YonetimController::class, 'peyAdimiGuncelle'])->name('yonetim.pey.guncelle');
    Route::post('/pey-adimlari/{peyAdimi}/sil', [YonetimController::class, 'peyAdimiSil'])->name('yonetim.pey.sil');
    Route::get('/uyeler', [YonetimController::class, 'uyeler'])->name('yonetim.uyeler');
    Route::post('/uyeler', [YonetimController::class, 'uyeEkle'])->name('yonetim.uye.ekle');
    Route::get('/uye/{user}', [YonetimController::class, 'uye'])->name('yonetim.uye');
    Route::post('/uye/{user}/guncelle', [YonetimController::class, 'uyeGuncelle'])->name('yonetim.uye.guncelle');
    Route::post('/uye/{user}/engelle', [YonetimController::class, 'uyeEngelle'])->name('yonetim.uye.engelle');
    Route::get('/teklifler', [YonetimController::class, 'teklifler'])->name('yonetim.teklifler');
    Route::get('/iletisim', [YonetimController::class, 'iletisim'])->name('yonetim.iletisim');
    Route::post('/iletisim', [YonetimController::class, 'iletisimGuncelle'])->name('yonetim.iletisim.guncelle');
    Route::get('/ekspertiz', [YonetimController::class, 'ekspertiz'])->name('yonetim.ekspertiz');
    Route::post('/ekspertiz', [YonetimController::class, 'ekspertizGuncelle'])->name('yonetim.ekspertiz.guncelle');
});
