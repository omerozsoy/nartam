<?php

declare(strict_types=1);

namespace App\Cekirdek;

/**
 * Basit görünüm (view) motoru. PHP şablonlarını render eder.
 */
final class Gorunum
{
    /** Tek bir şablonu render edip string döner. */
    public static function yap(string $ad, array $veri = []): string
    {
        extract($veri, EXTR_SKIP);
        ob_start();
        require \dirname(__DIR__) . '/views/' . $ad . '.php';

        return (string) ob_get_clean();
    }

    /** İçerik şablonunu düzen (layout) içine sararak tam sayfa döner. */
    public static function sayfa(string $ad, array $veri, string $baslik, ?array $kullanici): string
    {
        $icerik = self::yap($ad, $veri);

        return self::yap('duzen', [
            'icerik' => $icerik,
            'baslik' => $baslik,
            'kullanici' => $kullanici,
        ]);
    }
}
