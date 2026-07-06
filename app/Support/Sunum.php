<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Durum;
use App\Models\Ilan;
use Carbon\CarbonImmutable;

/**
 * Ilan modelini ekran/JSON için düz diziye çevirir.
 * Hem liste görünümü hem canlı güncelleme API'si aynı özeti kullanır.
 */
class Sunum
{
    public static function ilan(Ilan $ilan, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();

        $durum = $ilan->durum($now);
        $bitis = $ilan->bitis_zamani;
        $sonrakiDusus = $ilan->sonrakiDususZamani($now);
        $fiyat = $ilan->guncelFiyat($now);
        $minTeklif = $durum === Durum::KAPANDI ? $fiyat : $ilan->minTeklif($now);

        return [
            'id' => $ilan->id,
            'baslik' => $ilan->baslik,
            'durum' => $durum->value,
            'durumEtiket' => $durum->etiket(),
            'guncelFiyat' => $fiyat,
            'guncelFiyatBicim' => number_format($fiyat, 0, ',', '.') . ' ₺',
            'minTeklif' => $minTeklif,
            'minTeklifBicim' => number_format($minTeklif, 0, ',', '.') . ' ₺',
            'bitisTs' => $bitis?->getTimestamp(),
            'sonrakiDususTs' => $sonrakiDusus?->getTimestamp(),
            'sonTeklifSahibi' => $ilan->son_teklif_sahibi,
        ];
    }
}
