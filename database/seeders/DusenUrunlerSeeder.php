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
            // [başlık, başlangıç fiyatı, saatlik düşüş, rezerv, kaç saat önce başladı]
            ['Osmanlı Gümüş Tepsi',        8000,  400, 4000, 1],
            ['El Dokuması İpek Halı',      25000, 1000, 15000, 2],
            ['İmzalı Litografi Baskı',     3000,  200, 1500, 0],
            ['Bronz Heykel "Düşünen"',     15000, 750, 9000, 4],
            ['Art Deco Masa Saati',        4500,  250, 2500, 3],
        ];

        foreach ($urunler as [$baslik, $fiyat, $dusus, $rezerv, $saat]) {
            Ilan::firstOrCreate(
                ['baslik' => $baslik],
                [
                    'baslangic_fiyati' => $fiyat,
                    'saatlik_dusus' => $dusus,
                    'rezerv_fiyat' => $rezerv,
                    'baslangic_zamani' => $now->subHours($saat),
                ]
            );
        }
    }
}
