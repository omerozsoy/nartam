<?php

declare(strict_types=1);

namespace App\Cekirdek;

use PDO;

/**
 * SQLite (PDO) bağlantısı. Tek örnek (lazy singleton).
 */
final class Veritabani
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $yol = NARTAM_DB;
            $dizin = \dirname($yol);
            if (!is_dir($dizin)) {
                mkdir($dizin, 0777, true);
            }

            $pdo = new PDO('sqlite:' . $yol);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA foreign_keys = ON');

            self::$pdo = $pdo;

            // Şema yoksa otomatik uygula (yeni sunucuda 500 yerine boş ama çalışan
            // uygulama). Örnek veri/yönetici için yine `php bin/kur.php` çalıştırın.
            $sema = \dirname(__DIR__, 2) . '/db/schema.sql';
            if (is_file($sema)) {
                $pdo->exec(file_get_contents($sema));
            }
        }

        return self::$pdo;
    }
}
