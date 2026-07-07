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
    public static function ilan(Ilan $ilan, ?CarbonImmutable $now = null, ?int $benimId = null, bool $teklifVerdim = false, ?int $benimMax = null): array
    {
        $now ??= CarbonImmutable::now();

        $durum = $ilan->durum($now);

        // Giriş yapan kullanıcının bu ilandaki durumu: önde / geçildi / (yok)
        $benimDurum = null;
        if ($benimId !== null) {
            if ((int) $ilan->lider_id === $benimId) {
                $benimDurum = 'onde';
            } elseif ($teklifVerdim) {
                $benimDurum = 'gecildi';
            }
        }
        // Teklifi geçildiyse gizli maksimumu artık gösterme.
        if ($benimDurum === 'gecildi') {
            $benimMax = null;
        }

        $bitis = $ilan->bitis_zamani;
        $fiyat = $ilan->guncelFiyat($now);
        $minTeklif = $durum === Durum::KAPANDI ? $fiyat : $ilan->minTeklif($now);

        // Düşüş fazında fiyat rezerv (taban) fiyata indiyse artık düşmez.
        $tabanaUlasti = $durum === Durum::DUSUYOR && $fiyat <= (int) $ilan->rezerv_fiyat;
        $sonrakiDusus = $tabanaUlasti ? null : $ilan->sonrakiDususZamani($now);

        // Başlangıca göre yüzde düşüş (yalnızca düşüş fazında).
        $baslangic = (int) $ilan->baslangic_fiyati;
        $dususYuzde = ($durum === Durum::DUSUYOR && $baslangic > 0)
            ? (int) round(($baslangic - $fiyat) / $baslangic * 100)
            : 0;

        // Teklifsiz + son 12 saat: fiyat düşüyor (tabana inince ayrı etiket).
        $durumEtiket = $durum === Durum::DUSUYOR
            ? ($tabanaUlasti ? 'Taban Fiyata Ulaşıldı' : 'Son 12 Saat · Fiyat Düşüyor')
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
            'tabanaUlasti' => $tabanaUlasti,
            'dususYuzde' => $dususYuzde,
            'sonTeklifSahibi' => Ad::gizle($ilan->son_teklif_sahibi),
            'liderId' => $ilan->lider_id,
            'benimDurum' => $benimDurum,
            'benimMax' => $benimMax,
            'benimMaxBicim' => $benimMax ? number_format($benimMax, 0, ',', '.') . ' ₺' : null,
            'teklifSayisi' => $ilan->teklifler_count ?? $ilan->teklifler()->count(),
        ];
    }
}
