<?php

namespace Database\Seeders;

use App\Models\Ilan;
use Illuminate\Database\Seeder;

/**
 * Açık artırmadaki (teklif almış) ama lot numarası olmayan ilanlara,
 * açık artırmaya giriş sırasına göre sıradaki numarayı atar.
 * Tekrar çalıştırılabilir (yalnızca lot_no'su boş olanları doldurur):
 *   php artisan db:seed --class=LotNoAtaSeeder --force
 */
class LotNoAtaSeeder extends Seeder
{
    public function run(): void
    {
        $sonrakiNo = (int) Ilan::max('lot_no'); // mevcut en yüksek numaradan devam et

        $eksik = Ilan::whereNotNull('ilk_teklif_zamani')
            ->whereNull('lot_no')
            ->orderBy('ilk_teklif_zamani')
            ->get();

        foreach ($eksik as $ilan) {
            $ilan->update(['lot_no' => ++$sonrakiNo]);
        }

        $this->command?->info($eksik->count() . ' ilana lot numarası atandı.');
    }
}
