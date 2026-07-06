<?php

namespace Database\Seeders;

use App\Models\Ilan;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Düşüş fazında (teklifsiz) 5 örnek ürün.
 * Tekrar çalıştırılabilir (başlığa göre firstOrCreate — çoğaltmaz):
 *   php artisan db:seed --class=DusenUrunlerSeeder --force
 */
class DusenUrunlerSeeder extends Seeder
{
    public function run(): void
    {
        $now = CarbonImmutable::now();

        $urunler = [
            // [başlık, alt başlık, başlangıç fiyatı, saatlik düşüş, rezerv, kaç saat önce başladı]
            ['Osmanlı Gümüş Tepsi',    'El işçiliği, 19. yüzyıl',        8000,  400, 4000, 1],
            ['El Dokuması İpek Halı',  'Hereke, ipek üzerine',          25000, 1000, 15000, 2],
            ['İmzalı Litografi Baskı', 'Sınırlı sayıda, numaralı',      3000,  200, 1500, 0],
            ['Bronz Heykel "Düşünen"', 'Bronz döküm, imzalı',           15000, 750, 9000, 4],
            ['Art Deco Masa Saati',    'Fransız, 1920\'ler',            4500,  250, 2500, 3],
        ];

        foreach ($urunler as [$baslik, $alt, $fiyat, $dusus, $rezerv, $saat]) {
            Ilan::firstOrCreate(
                ['baslik' => $baslik],
                [
                    'alt_baslik' => $alt,
                    'baslangic_fiyati' => $fiyat,
                    'saatlik_dusus' => $dusus,
                    'rezerv_fiyat' => $rezerv,
                    'baslangic_zamani' => $now->subHours($saat),
                ]
            );
        }
    }
}
