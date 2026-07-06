<?php

declare(strict_types=1);

namespace App\Cekirdek;

use PDO;

/**
 * Veritabanı bağlantısı (PDO). Sürücü .env'deki DB_DRIVER ile seçilir:
 * 'mysql' (varsayılan, üretim) veya 'sqlite' (yerel geliştirme).
 */
final class Veritabani
{
    private static ?PDO $pdo = null;

    public static function driver(): string
    {
        return Config::get('DB_DRIVER', 'mysql') === 'sqlite' ? 'sqlite' : 'mysql';
    }

    /** Aktif sürücüye ait şema dosyasının yolu. */
    public static function semaYolu(): string
    {
        return \dirname(__DIR__, 2) . '/db/schema.' . self::driver() . '.sql';
    }

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::baglan();
        }

        return self::$pdo;
    }

    private static function baglan(): PDO
    {
        $secenekler = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if (self::driver() === 'sqlite') {
            $yol = NARTAM_DB;
            $dizin = \dirname($yol);
            if (!is_dir($dizin)) {
                mkdir($dizin, 0777, true);
            }
            $pdo = new PDO('sqlite:' . $yol, null, null, $secenekler);
            $pdo->exec('PRAGMA foreign_keys = ON');

            return $pdo;
        }

        // MySQL
        $host = Config::get('DB_HOST', '127.0.0.1');
        $port = Config::get('DB_PORT', '3306');
        $ad = Config::get('DB_NAME', 'nartam');
        $kullanici = Config::get('DB_USER', 'root');
        $sifre = Config::get('DB_PASS', '');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $ad);

        return new PDO($dsn, $kullanici, $sifre, $secenekler);
    }
}
