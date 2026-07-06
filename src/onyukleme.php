<?php

declare(strict_types=1);

/*
 * Önyükleme: autoloader, yardımcılar, oturum ve yapılandırma.
 * Hem web (public/index.php) hem CLI (bin/*.php) bunu yükler.
 */

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Istanbul');

// Veritabanı dosyasının yolu (data/ dizini .gitignore'da).
if (!defined('NARTAM_DB')) {
    define('NARTAM_DB', dirname(__DIR__) . '/data/nartam.sqlite');
}

// Basit PSR-4 autoloader: App\ -> src/
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

// Ortam yapılandırması (.env varsa yüklenir; gerçek ortam değişkenleri önceliklidir).
\App\Cekirdek\Config::yukle(dirname(__DIR__) . '/.env');

require __DIR__ . '/yardimcilar.php';

// Oturum yalnızca web bağlamında başlar (CLI'de header gönderilemez).
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}
