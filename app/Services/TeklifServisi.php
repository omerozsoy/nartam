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

        // İlk teklif mi? (henüz lider yok) — düşüş fazında da açık artırmada da olabilir.
        $ilkTeklif = ! $ilan->teklifAldi();

        // --- Doğrulama ---
        if ($durum === Durum::YAKINDA) {
            throw $this->hata('Müzayede henüz başlamadı, teklif verilemez.');
        }
        if ($durum === Durum::KAPANDI) {
            throw $this->hata('Müzayede kapandı, teklif verilemez.');
        }

        if ($ilkTeklif) {
            // İlk teklif: o anki geçerli fiyattan (düşen fiyat ya da başlangıç) verilir.
            $taban = $ilan->guncelFiyat($now);
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
        DB::transaction(function () use ($ilan, $kullanici, $miktar, $now, $ilkTeklif) {
            if ($ilkTeklif) {
                // İlk teklif: normal açık artırma başlar. Görünen fiyat = o anki geçerli fiyat.
                // Bitiş zamanı SABİT kalır (24 saat başlatılmaz).
                $ilan->guncel_teklif = $ilan->guncelFiyat($now);
                $ilan->lider_id = $kullanici->id;
                $ilan->lider_max = $miktar;
                $ilan->son_teklif_sahibi = $kullanici->name;
                $this->antiSnipe($ilan, $now);
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

                $this->antiSnipe($ilan, $now);
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

    /** Son dakikada gelen teklif kapanışı uzatır (anti-snipe). */
    private function antiSnipe(Ilan $ilan, CarbonImmutable $now): void
    {
        if ($ilan->bitis_zamani === null) {
            return;
        }
        $kalan = $ilan->bitis_zamani->getTimestamp() - $now->getTimestamp();
        if ($kalan < Ilan::ANTI_SNIPE_ESIK) {
            $ilan->bitis_zamani = $now->addSeconds(Ilan::ANTI_SNIPE_UZATMA);
        }
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
