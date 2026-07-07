<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Durum;
use App\Models\Ilan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Teklif verme akışı — vekaleten (proxy / otomatik) teklif.
 *
 * Girilen tutar kullanıcının GİZLİ MAKSİMUM'udur. Görünen fiyat, ikinci en yüksek
 * maksimumun bir adım üstüne kadar yükselir (lider maksımını aşamaz). Lider yeniden
 * teklif verirse yalnızca kendi maksimumunu yükseltir, fiyat değişmez.
 */
class TeklifServisi
{
    /**
     * @throws ValidationException Teklif geçersizse.
     */
    public function teklifVer(Ilan $ilan, User $kullanici, int $miktar): Ilan
    {
        $now = CarbonImmutable::now();
        $durum = $ilan->durum($now);

        // --- Doğrulama ---
        if ($durum === Durum::KAPANDI) {
            throw $this->hata('İlan kapandı, teklif verilemez.');
        }

        if ($durum === Durum::DUSUYOR) {
            $taban = $ilan->dusenFiyat($now);
            if ($miktar < $taban) {
                throw $this->hata('Teklif en az ' . $this->para($taban) . ' olmalı.');
            }
        } elseif ((int) $ilan->lider_id === $kullanici->id) {
            // Lider zaten önde — sadece maksimumunu yükseltebilir
            if ($miktar <= (int) $ilan->lider_max) {
                throw $this->hata('Zaten öndesiniz. Maksimum teklifiniz ' . $this->para((int) $ilan->lider_max)
                    . '. Artırmak için daha yüksek bir tutar girin.');
            }
        } else {
            $enAz = $ilan->minTeklif($now);
            if ($miktar < $enAz) {
                throw $this->hata('Teklif en az ' . $this->para($enAz) . ' olmalı.');
            }
        }

        // --- Uygula ---
        DB::transaction(function () use ($ilan, $kullanici, $miktar, $now, $durum) {
            if ($durum === Durum::DUSUYOR) {
                // İlk teklif: açık artırma başlar. Görünen fiyat = o anki düşmüş fiyat (taban).
                $taban = $ilan->dusenFiyat($now);
                $ilan->ilk_teklif_zamani = $now;
                $ilan->bitis_zamani = $now->addSeconds(Ilan::ACIK_ARTIRMA_SURESI);
                $ilan->lot_no = (Ilan::max('lot_no') ?? 0) + 1;
                $ilan->guncel_teklif = $taban;
                $ilan->lider_id = $kullanici->id;
                $ilan->lider_max = $miktar;
                $ilan->son_teklif_sahibi = $kullanici->name;
            } elseif ((int) $ilan->lider_id === $kullanici->id) {
                // Lider kendi maksimumunu yükseltir; görünen fiyat değişmez
                $ilan->lider_max = $miktar;
            } else {
                $P = (int) $ilan->guncel_teklif;
                $Lmax = (int) $ilan->lider_max;

                if ($miktar > $Lmax) {
                    // Yeni lider: fiyat eski liderin maksının bir adım üstüne çıkar (yeni maksı aşmadan)
                    $ilan->guncel_teklif = min($miktar, $Lmax + Ilan::artirimAdimi($Lmax));
                    $ilan->lider_id = $kullanici->id;
                    $ilan->lider_max = $miktar;
                    $ilan->son_teklif_sahibi = $kullanici->name;
                } else {
                    // Lider korunur; sistem lider adına otomatik pey verir (rakibin bir adım üstü)
                    $ilan->guncel_teklif = min($Lmax, $miktar + Ilan::artirimAdimi($P));
                }

                // Anti-snipe
                $kalan = $ilan->bitis_zamani->getTimestamp() - $now->getTimestamp();
                if ($kalan < Ilan::ANTI_SNIPE_ESIK) {
                    $ilan->bitis_zamani = $now->addSeconds(Ilan::ANTI_SNIPE_UZATMA);
                }
            }

            $ilan->save();

            $ilan->teklifler()->create([
                'kullanici_id' => $kullanici->id,
                'miktar' => $miktar, // kullanıcının maksimumu
                'zaman' => $now,
            ]);
        });

        return $ilan;
    }

    private function hata(string $mesaj): ValidationException
    {
        return ValidationException::withMessages(['miktar' => $mesaj]);
    }

    private function para(int $tutar): string
    {
        return number_format($tutar, 0, ',', '.') . ' ₺';
    }
}
