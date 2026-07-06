<?php

declare(strict_types=1);

namespace App\Depo;

use App\Cekirdek\Veritabani;
use DateTimeImmutable;

/**
 * Kullanıcı kayıtlarına erişim.
 */
final class KullaniciDepo
{
    public function epostaIle(string $eposta): ?array
    {
        $s = Veritabani::pdo()->prepare('SELECT * FROM kullanicilar WHERE eposta = ?');
        $s->execute([$eposta]);

        return $s->fetch() ?: null;
    }

    public function idIle(int $id): ?array
    {
        $s = Veritabani::pdo()->prepare('SELECT * FROM kullanicilar WHERE id = ?');
        $s->execute([$id]);

        return $s->fetch() ?: null;
    }

    public function olustur(string $eposta, string $ad, string $sifreHash, string $rol = 'uye'): int
    {
        $s = Veritabani::pdo()->prepare(
            'INSERT INTO kullanicilar (eposta, ad, sifre_hash, rol, olusturuldu) VALUES (?, ?, ?, ?, ?)'
        );
        $s->execute([$eposta, $ad, $sifreHash, $rol, (new DateTimeImmutable())->format('c')]);

        return (int) Veritabani::pdo()->lastInsertId();
    }
}
