<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Bir ilanın yaşam döngüsündeki durumu.
 */
enum Durum: string
{
    case YAKINDA = 'yakinda';           // Müzayede henüz başlamadı; teklif kapalı
    case DUSUYOR = 'dusuyor';           // Teklifsiz; son 12 saatte fiyat düşüyor
    case ACIK_ARTIRMA = 'acik_artirma'; // Açık/yükselen açık artırma
    case KAPANDI = 'kapandi';           // Süre doldu; en yüksek teklif kazandı

    public function etiket(): string
    {
        return match ($this) {
            self::YAKINDA => 'Yakında',
            self::DUSUYOR => 'Fiyat Düşüyor',
            self::ACIK_ARTIRMA => 'Açık Artırma',
            self::KAPANDI => 'Müzayede Bitmiştir',
        };
    }
}
