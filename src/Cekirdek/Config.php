<?php

declare(strict_types=1);

namespace App\Cekirdek;

/**
 * Basit yapılandırma: gerçek ortam değişkenleri > .env dosyası > varsayılan.
 */
final class Config
{
    private static array $veri = [];

    public static function yukle(string $dosya): void
    {
        if (!is_file($dosya)) {
            return;
        }

        foreach (file($dosya, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $satir) {
            $satir = trim($satir);
            if ($satir === '' || str_starts_with($satir, '#')) {
                continue;
            }
            [$anahtar, $deger] = array_pad(explode('=', $satir, 2), 2, '');
            self::$veri[trim($anahtar)] = trim(trim($deger), "\"'");
        }
    }

    public static function get(string $anahtar, ?string $varsayilan = null): ?string
    {
        $ortam = getenv($anahtar);
        if ($ortam !== false && $ortam !== '') {
            return $ortam;
        }

        return self::$veri[$anahtar] ?? $varsayilan;
    }
}
