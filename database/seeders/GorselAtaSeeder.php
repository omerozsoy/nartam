<?php

namespace Database\Seeders;

use App\Models\Ilan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * public/urunler/ altındaki görselleri ilanlara rastgele (her birine farklı) atar.
 * Mevcut veriyi silmez, sadece gorsel_url'i günceller. Tekrar çalıştırılabilir:
 *   php artisan db:seed --class=GorselAtaSeeder --force
 */
class GorselAtaSeeder extends Seeder
{
    public function run(): void
    {
        $gorseller = collect(glob(public_path('urunler/*.jpg')))
            ->map(fn (string $p) => '/urunler/' . basename($p))
            ->shuffle()
            ->values();

        if ($gorseller->isEmpty()) {
            $this->command?->warn('public/urunler/ boş — görsel atanmadı.');
            return;
        }

        Ilan::orderBy('id')->get()->each(function (Ilan $ilan, int $i) use ($gorseller): void {
            $ilan->update(['gorsel_url' => $gorseller[$i % $gorseller->count()]]);
        });

        $this->command?->info($gorseller->count() . ' görsel, ilanlara atandı.');
    }
}
