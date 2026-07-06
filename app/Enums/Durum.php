<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Bir ilanın yaşam döngüsündeki durumu.
 */
enum Durum: string
{
    case DUSUYOR = 'dusuyor';           // Teklif yok; fiyat saatlik düşüyor (Hollanda usulü)
    case ACIK_ARTIRMA = 'acik_artirma'; // İlk teklif geldi; yükselen açık artırma, 24s geri sayım
    case KAPANDI = 'kapandi';           // Süre doldu; en yüksek teklif kazandı

    public function etiket(): string
    {
        return match ($this) {
            self::DUSUYOR => 'Fiyat Düşüyor',
            self::ACIK_ARTIRMA => 'Açık Artırma',
            self::KAPANDI => 'Kapandı',
        };
    }
}
