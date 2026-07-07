<?php

namespace Database\Seeders;

use App\Models\Ilan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Kullanıcılar ---
        $mehmet = User::create([
            'name' => 'Mehmet',
            'email' => 'mehmet@nartam.test',
            'password' => Hash::make('parola123'),
        ]);
        $ayse = User::create([
            'name' => 'Ayşe',
            'email' => 'ayse@nartam.test',
            'password' => Hash::make('parola123'),
        ]);
        User::create([
            'name' => 'Yönetici',
            'email' => 'admin@nartam.test',
            'password' => Hash::make('admin123'),
            'rol' => 'yonetici',
        ]);

        $now = CarbonImmutable::now();

        // --- Aktif müzayede ---
        $muzayede = \App\Models\Muzayede::create([
            'no' => '407',
            'ad' => 'Sanat & Antika',
            'baslangic' => $now->subDay(), // teklifler açık
            'bitis' => $now->addDays(2),
            'esik_lot' => 10,
            'aralik1' => 2,
            'aralik2' => 1,
            'aktif' => true,
        ]);

        // --- Lot 1: açık artırma, teklif almış (proxy) ---
        $t1 = $now->subMinutes(90);
        $t2 = $now->subMinutes(40);
        $ilan1 = Ilan::create([
            'muzayede_id' => $muzayede->id,
            'baslik' => 'Yağlı Boya Tablo',
            'lot_no' => 1,
            'alt_baslik' => 'Tuval üzerine, imzalı',
            'baslangic_fiyati' => 12000,
            'rezerv_fiyat' => 8000,
            'bitis_zamani' => $now->addDays(2),
            'guncel_teklif' => 12000 + Ilan::artirimAdimi(12000),
            'lider_id' => $ayse->id,
            'lider_max' => 12500,
            'son_teklif_sahibi' => 'Ayşe',
        ]);
        $ilan1->teklifler()->createMany([
            ['kullanici_id' => $mehmet->id, 'miktar' => 12000, 'zaman' => $t1],
            ['kullanici_id' => $ayse->id, 'miktar' => 12500, 'zaman' => $t2],
        ]);

        // --- Lot 2: teklifsiz, kapanışa uzak (açık artırma, başlangıç fiyatından) ---
        Ilan::create([
            'muzayede_id' => $muzayede->id,
            'baslik' => 'Antika Porselen Vazo',
            'lot_no' => 2,
            'alt_baslik' => 'Çin, el boyaması',
            'baslangic_fiyati' => 1000,
            'rezerv_fiyat' => 500,
            'bitis_zamani' => $now->addDays(2),
        ]);

        // --- Lot 3: teklifsiz, kapanışa 6 saat (son 12 saat: fiyat düşüyor) ---
        Ilan::create([
            'muzayede_id' => $muzayede->id,
            'baslik' => 'Gümüş Şamdan Takımı',
            'lot_no' => 3,
            'alt_baslik' => 'Osmanlı dönemi',
            'baslangic_fiyati' => 20000,
            'rezerv_fiyat' => 10000,
            'bitis_zamani' => $now->addHours(6),
        ]);

        // Ürünlere görsel ata (public/urunler/)
        $this->call(GorselAtaSeeder::class);
    }
}
