<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Gizlilik: teklif verenlerin adını yıldızlar (harf sayısı kadar *).
 */
class Ad
{
    public static function gizle(?string $ad): ?string
    {
        if ($ad === null || $ad === '') {
            return $ad;
        }

        // Boşlukları koru, harf/rakamları * yap: "Mehmet Yılmaz" -> "****** ******"
        return preg_replace('/\S/u', '*', $ad);
    }
}
