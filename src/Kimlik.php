<?php

declare(strict_types=1);

namespace App;

use App\Depo\KullaniciDepo;
use DomainException;

/**
 * Kimlik doğrulama: kayıt, giriş, çıkış ve oturumdaki kullanıcı.
 */
final class Kimlik
{
    public function __construct(
        private readonly KullaniciDepo $depo = new KullaniciDepo(),
    ) {
    }

    public function kayit(string $eposta, string $ad, string $sifre): array
    {
        $eposta = mb_strtolower(trim($eposta));
        $ad = trim($ad);

        if (!filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Geçerli bir e-posta girin.');
        }
        if ($ad === '') {
            throw new DomainException('Ad boş olamaz.');
        }
        if (mb_strlen($sifre) < 6) {
            throw new DomainException('Şifre en az 6 karakter olmalı.');
        }
        if ($this->depo->epostaIle($eposta) !== null) {
            throw new DomainException('Bu e-posta zaten kayıtlı.');
        }

        $id = $this->depo->olustur($eposta, $ad, password_hash($sifre, PASSWORD_DEFAULT));
        $this->oturumAc($id);

        return $this->depo->idIle($id);
    }

    public function giris(string $eposta, string $sifre): array
    {
        $eposta = mb_strtolower(trim($eposta));
        $kullanici = $this->depo->epostaIle($eposta);

        if ($kullanici === null || !password_verify($sifre, $kullanici['sifre_hash'])) {
            throw new DomainException('E-posta veya şifre hatalı.');
        }

        $this->oturumAc((int) $kullanici['id']);

        return $kullanici;
    }

    public function cikis(): void
    {
        unset($_SESSION['kullanici_id']);
    }

    public function mevcut(): ?array
    {
        $id = $_SESSION['kullanici_id'] ?? null;

        return $id !== null ? $this->depo->idIle((int) $id) : null;
    }

    public function yonetici(): bool
    {
        return ($this->mevcut()['rol'] ?? null) === 'yonetici';
    }

    private function oturumAc(int $kullaniciId): void
    {
        session_regenerate_id(true);
        $_SESSION['kullanici_id'] = $kullaniciId;
    }
}
