<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Durum;
use App\Models\Ilan;
use App\Support\Ad;
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

        // Düşüş fazında etiket periyodu belirtir: "Her Dakika Fiyat Düşüyor" vb.
        $durumEtiket = $durum === Durum::DUSUYOR
            ? match ($ilan->periyot()) {
                1 => 'Her Saniye Fiyat Düşüyor',
                60 => 'Her Dakika Fiyat Düşüyor',
                default => 'Her Saat Fiyat Düşüyor',
            }
            : $durum->etiket();

        return [
            'id' => $ilan->id,
            'lotNo' => $ilan->lot_no,
            'baslik' => $ilan->baslik,
            'altBaslik' => $ilan->alt_baslik,
            'gorselUrl' => $ilan->gorsel_url,
            'durum' => $durum->value,
            'durumEtiket' => $durumEtiket,
            'guncelFiyat' => $fiyat,
            'guncelFiyatBicim' => number_format($fiyat, 0, ',', '.') . ' ₺',
            'baslangicFiyatiBicim' => number_format($ilan->baslangic_fiyati, 0, ',', '.') . ' ₺',
            'minTeklif' => $minTeklif,
            'minTeklifBicim' => number_format($minTeklif, 0, ',', '.') . ' ₺',
            'bitisTs' => $bitis?->getTimestamp(),
            'sonrakiDususTs' => $sonrakiDusus?->getTimestamp(),
            'sonTeklifSahibi' => Ad::gizle($ilan->son_teklif_sahibi),
            'liderId' => $ilan->lider_id,
            'teklifSayisi' => $ilan->teklifler_count ?? $ilan->teklifler()->count(),
        ];
    }
}
