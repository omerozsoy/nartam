<?php

declare(strict_types=1);

namespace App;

use DateInterval;
use DateTimeImmutable;
use DomainException;

/**
 * Tek bir müzayede ilanı. İki fazlı çalışır:
 *
 *   1) DÜŞEN FİYAT (Dutch): teklif gelene kadar fiyat her saat {@see $saatlikDusus}
 *      kadar düşer, {@see $rezervFiyat} tabanında durur.
 *   2) AÇIK ARTIRMA (English): ilk teklif geldiği an, o anki düşmüş fiyat taban
 *      olur ve 24 saatlik geri sayım başlar. Sonraki teklifler kademeli artırım
 *      adımıyla yükselir. Son {@see ANTI_SNIPE_ESIK} saniyede gelen teklif
 *      sayacı uzatır (anti-snipe).
 */
final class Ilan
{
    /** Açık artırma fazının süresi (saniye). */
    private const ACIK_ARTIRMA_SURESI = 24 * 60 * 60;

    /** Bu süreden az kala gelen teklif sayacı uzatır (saniye). */
    private const ANTI_SNIPE_ESIK = 2 * 60;

    /** Anti-snipe tetiklenince sayaç bu kadara çekilir (saniye). */
    private const ANTI_SNIPE_UZATMA = 2 * 60;

    private ?DateTimeImmutable $ilkTeklifZamani = null;
    private ?DateTimeImmutable $bitisZamani = null;
    private ?int $guncelTeklif = null;
    private ?string $sonTeklifSahibi = null;

    /** @var list<array{sahip: string, miktar: int, zaman: DateTimeImmutable}> */
    private array $teklifler = [];

    public function __construct(
        public readonly string $baslik,
        public readonly int $baslangicFiyati,
        public readonly int $saatlikDusus,
        public readonly int $rezervFiyat,
        public readonly DateTimeImmutable $baslangicZamani,
        public readonly ?int $id = null,
    ) {
    }

    /**
     * Veritabanı satırından (kalıcı durumdan) bir Ilan yeniden canlandırır.
     *
     * @param array{
     *     id: int, baslik: string, baslangicFiyati: int, saatlikDusus: int,
     *     rezervFiyat: int, baslangicZamani: DateTimeImmutable,
     *     ilkTeklifZamani: ?DateTimeImmutable, bitisZamani: ?DateTimeImmutable,
     *     guncelTeklif: ?int, sonTeklifSahibi: ?string
     * } $d
     */
    public static function fromState(array $d): self
    {
        $ilan = new self(
            $d['baslik'],
            $d['baslangicFiyati'],
            $d['saatlikDusus'],
            $d['rezervFiyat'],
            $d['baslangicZamani'],
            $d['id'],
        );
        $ilan->ilkTeklifZamani = $d['ilkTeklifZamani'];
        $ilan->bitisZamani = $d['bitisZamani'];
        $ilan->guncelTeklif = $d['guncelTeklif'];
        $ilan->sonTeklifSahibi = $d['sonTeklifSahibi'];

        return $ilan;
    }

    public function durum(DateTimeImmutable $now): Durum
    {
        if ($this->ilkTeklifZamani === null) {
            return Durum::DUSUYOR;
        }

        return $now >= $this->bitisZamani ? Durum::KAPANDI : Durum::ACIK_ARTIRMA;
    }

    /** Düşüş fazındaki anlık fiyat (rezervde taban yapar). */
    public function dusenFiyat(DateTimeImmutable $now): int
    {
        $gecenSaat = intdiv(max(0, $now->getTimestamp() - $this->baslangicZamani->getTimestamp()), 3600);
        $fiyat = $this->baslangicFiyati - ($gecenSaat * $this->saatlikDusus);

        return max($this->rezervFiyat, $fiyat);
    }

    /** Ekranda gösterilecek geçerli fiyat (faza göre). */
    public function guncelFiyat(DateTimeImmutable $now): int
    {
        return $this->durum($now) === Durum::DUSUYOR
            ? $this->dusenFiyat($now)
            : (int) $this->guncelTeklif;
    }

    /** Bu an için geçerli en düşük kabul edilebilir teklif. */
    public function minTeklif(DateTimeImmutable $now): int
    {
        if ($this->durum($now) === Durum::DUSUYOR) {
            return $this->dusenFiyat($now);
        }

        return (int) $this->guncelTeklif + self::artirimAdimi((int) $this->guncelTeklif);
    }

    /** Kademeli artırım tablosu. */
    public static function artirimAdimi(int $fiyat): int
    {
        return match (true) {
            $fiyat < 1000 => 50,
            $fiyat < 5000 => 100,
            default => 250,
        };
    }

    /**
     * Teklif ver. Geçersizse {@see DomainException} fırlatır.
     */
    public function teklifVer(string $sahip, int $miktar, DateTimeImmutable $now): void
    {
        $durum = $this->durum($now);

        if ($durum === Durum::KAPANDI) {
            throw new DomainException('İlan kapandı, teklif verilemez.');
        }

        $enAz = $this->minTeklif($now);
        if ($miktar < $enAz) {
            throw new DomainException(sprintf('Teklif en az %d ₺ olmalı.', $enAz));
        }

        if ($durum === Durum::DUSUYOR) {
            // İlk teklif: o anki düşmüş fiyat taban olur, açık artırma başlar.
            $this->ilkTeklifZamani = $now;
            $this->bitisZamani = $now->add(new DateInterval('PT' . self::ACIK_ARTIRMA_SURESI . 'S'));
        } else {
            // Anti-snipe: bitişe çok az kala gelen teklif sayacı uzatır.
            $kalan = $this->bitisZamani->getTimestamp() - $now->getTimestamp();
            if ($kalan < self::ANTI_SNIPE_ESIK) {
                $this->bitisZamani = $now->add(new DateInterval('PT' . self::ANTI_SNIPE_UZATMA . 'S'));
            }
        }

        $this->guncelTeklif = $miktar;
        $this->sonTeklifSahibi = $sahip;
        $this->teklifler[] = ['sahip' => $sahip, 'miktar' => $miktar, 'zaman' => $now];
    }

    public function bitisZamani(): ?DateTimeImmutable
    {
        return $this->bitisZamani;
    }

    public function ilkTeklifZamani(): ?DateTimeImmutable
    {
        return $this->ilkTeklifZamani;
    }

    /** En yüksek teklifin tutarı (henüz teklif yoksa null). */
    public function guncelTeklifDegeri(): ?int
    {
        return $this->guncelTeklif;
    }

    /** Düşüş fazında bir sonraki fiyat düşüşünün zamanı (taban değilse). */
    public function sonrakiDususZamani(DateTimeImmutable $now): ?DateTimeImmutable
    {
        if ($this->durum($now) !== Durum::DUSUYOR || $this->dusenFiyat($now) <= $this->rezervFiyat) {
            return null;
        }

        $gecenSaat = intdiv(max(0, $now->getTimestamp() - $this->baslangicZamani->getTimestamp()), 3600);

        return $this->baslangicZamani->add(new DateInterval('PT' . (($gecenSaat + 1) * 3600) . 'S'));
    }

    public function sonTeklifSahibi(): ?string
    {
        return $this->sonTeklifSahibi;
    }

    /** @return list<array{sahip: string, miktar: int, zaman: DateTimeImmutable}> */
    public function teklifler(): array
    {
        return $this->teklifler;
    }
}
