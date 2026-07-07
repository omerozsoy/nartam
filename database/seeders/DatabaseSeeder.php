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

        // --- İlan 1: hâlâ düşüş fazında (3 saat önce başladı, 1000 -> 700) ---
        Ilan::create([
            'baslik' => 'Antika Porselen Vazo',
            'alt_baslik' => 'Çin, el boyaması',
            'baslangic_fiyati' => 1000,
            'saatlik_dusus' => 100,
            'rezerv_fiyat' => 500,
            'baslangic_zamani' => $now->subHours(3),
        ]);

        // --- İlan 2: açık artırmada (düşüş sırasında teklif geldi, sayaç işliyor) ---
        $t1 = $now->subMinutes(90); // fiyat 12000'ken Mehmet ilk teklifi verdi
        $t2 = $now->subMinutes(40); // Ayşe üstüne çıktı

        $ilan2 = Ilan::create([
            'baslik' => 'Yağlı Boya Tablo',
            'lot_no' => 1,
            'alt_baslik' => 'Tuval üzerine, imzalı',
            'baslangic_fiyati' => 12000,
            'saatlik_dusus' => 500,
            'rezerv_fiyat' => 8000,
            'baslangic_zamani' => $now->subHours(2),
            'ilk_teklif_zamani' => $t1,
            'bitis_zamani' => $t1->addSeconds(Ilan::ACIK_ARTIRMA_SURESI),
            // Proxy: Ayşe lider (gizli max 12500), görünen fiyat = Mehmet'in maksı + adım
            'guncel_teklif' => 12000 + Ilan::artirimAdimi(12000),
            'lider_id' => $ayse->id,
            'lider_max' => 12500,
            'son_teklif_sahibi' => 'Ayşe',
        ]);

        $ilan2->teklifler()->createMany([
            ['kullanici_id' => $mehmet->id, 'miktar' => 12000, 'zaman' => $t1],
            ['kullanici_id' => $ayse->id, 'miktar' => 12500, 'zaman' => $t2],
        ]);

        // Düşüş fazında 5 örnek ürün daha
        $this->call(DusenUrunlerSeeder::class);

        // Ürünlere görsel ata (public/urunler/)
        $this->call(GorselAtaSeeder::class);
    }
}
