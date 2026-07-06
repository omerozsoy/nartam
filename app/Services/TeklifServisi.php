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
 * Teklif verme akışı: iki fazlı domen kuralını uygular ve kalıcılaştırır.
 */
class TeklifServisi
{
    /**
     * @throws ValidationException Teklif geçersizse (kapalı ilan / yetersiz tutar).
     */
    public function teklifVer(Ilan $ilan, User $kullanici, int $miktar): Ilan
    {
        $now = CarbonImmutable::now();
        $durum = $ilan->durum($now);

        if ($durum === Durum::KAPANDI) {
            throw ValidationException::withMessages(['miktar' => 'İlan kapandı, teklif verilemez.']);
        }

        $enAz = $ilan->minTeklif($now);
        if ($miktar < $enAz) {
            throw ValidationException::withMessages([
                'miktar' => sprintf('Teklif en az %s ₺ olmalı.', number_format($enAz, 0, ',', '.')),
            ]);
        }

        DB::transaction(function () use ($ilan, $kullanici, $miktar, $now, $durum) {
            if ($durum === Durum::DUSUYOR) {
                // İlk teklif: o anki düşmüş fiyat taban olur, açık artırma başlar.
                $ilan->ilk_teklif_zamani = $now;
                $ilan->bitis_zamani = $now->addSeconds(Ilan::ACIK_ARTIRMA_SURESI);
            } else {
                // Anti-snipe: bitişe çok az kala gelen teklif sayacı uzatır.
                $kalan = $ilan->bitis_zamani->getTimestamp() - $now->getTimestamp();
                if ($kalan < Ilan::ANTI_SNIPE_ESIK) {
                    $ilan->bitis_zamani = $now->addSeconds(Ilan::ANTI_SNIPE_UZATMA);
                }
            }

            $ilan->guncel_teklif = $miktar;
            $ilan->son_teklif_sahibi = $kullanici->name;
            $ilan->save();

            $ilan->teklifler()->create([
                'kullanici_id' => $kullanici->id,
                'miktar' => $miktar,
                'zaman' => $now,
            ]);
        });

        return $ilan;
    }
}
