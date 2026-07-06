<?php

declare(strict_types=1);

namespace App;

/**
 * Bir ilanın yaşam döngüsündeki durumu.
 */
enum Durum: string
{
    /** Teklif yok; fiyat saatlik düşüyor (Hollanda usulü). */
    case DUSUYOR = 'dusuyor';

    /** İlk teklif geldi; yükselen açık artırma, 24 saat geri sayım. */
    case ACIK_ARTIRMA = 'acik_artirma';

    /** Süre doldu; en yüksek teklif kazandı (ya da hiç teklif olmadan kapandı). */
    case KAPANDI = 'kapandi';

    public function etiket(): string
    {
        return match ($this) {
            self::DUSUYOR => 'Fiyat Düşüyor',
            self::ACIK_ARTIRMA => 'Açık Artırma',
            self::KAPANDI => 'Kapandı',
        };
    }
}
