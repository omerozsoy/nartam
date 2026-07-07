<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Ilan;
use Illuminate\Console\Command;

/**
 * Tüm eserleri (ve tekliflerini) siler — yeni Excel yüklemesinden önce.
 * Kullanım: php artisan eserleri:sil
 */
class EserleriSil extends Command
{
    protected $signature = 'eserleri:sil';

    protected $description = 'Tüm eserleri ve tekliflerini siler';

    public function handle(): int
    {
        $sayi = Ilan::count();
        Ilan::query()->delete(); // teklifler cascade ile silinir

        $this->info("{$sayi} eser ve teklifleri silindi.");

        return self::SUCCESS;
    }
}
