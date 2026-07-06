<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\KazananBildirimi;
use App\Models\Ilan;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Süresi dolan (kapanan) müzayedelerin kazananlarına e-posta gönderir.
 * Zamanlayıcı ile her dakika çalışır (bkz. routes/console.php).
 */
class KapananlariBildir extends Command
{
    protected $signature = 'app:kapananlari-bildir';

    protected $description = 'Kapanan müzayedelerin kazananlarına e-posta gönderir';

    public function handle(): int
    {
        $simdi = CarbonImmutable::now();

        $kapananlar = Ilan::whereNotNull('ilk_teklif_zamani')
            ->where('bildirildi', false)
            ->where('bitis_zamani', '<=', $simdi)
            ->get();

        $sayac = 0;
        foreach ($kapananlar as $ilan) {
            $ustTeklif = $ilan->teklifler()->with('kullanici')->orderByDesc('miktar')->first();

            if ($ustTeklif?->kullanici) {
                Mail::to($ustTeklif->kullanici->email)
                    ->send(new KazananBildirimi($ilan, $ustTeklif->kullanici));
                $sayac++;
            }

            $ilan->update(['bildirildi' => true]);
        }

        $this->info("{$sayac} kazanan bilgilendirildi.");

        return self::SUCCESS;
    }
}
