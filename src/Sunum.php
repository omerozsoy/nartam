<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;

/**
 * Ilan domen nesnesini ekran/JSON için düz bir diziye çevirir.
 * Hem liste görünümü hem de canlı güncelleme API'si aynı özeti kullanır.
 */
final class Sunum
{
    public static function ilan(Ilan $ilan, DateTimeImmutable $now): array
    {
        $durum = $ilan->durum($now);
        $bitis = $ilan->bitisZamani();
        $sonrakiDusus = $ilan->sonrakiDususZamani($now);
        $fiyat = $ilan->guncelFiyat($now);
        $minTeklif = $durum === Durum::KAPANDI ? $fiyat : $ilan->minTeklif($now);

        return [
            'id' => $ilan->id,
            'baslik' => $ilan->baslik,
            'durum' => $durum->value,
            'durumEtiket' => $durum->etiket(),
            'guncelFiyat' => $fiyat,
            'guncelFiyatBicim' => para($fiyat),
            'minTeklif' => $minTeklif,
            'minTeklifBicim' => para($minTeklif),
            'bitisTs' => $bitis?->getTimestamp(),
            'sonrakiDususTs' => $sonrakiDusus?->getTimestamp(),
            'sonTeklifSahibi' => $ilan->sonTeklifSahibi(),
        ];
    }
}
