<?php

declare(strict_types=1);

namespace App\Depo;

use App\Cekirdek\Veritabani;
use App\Ilan;
use DateTimeImmutable;

/**
 * İlan kayıtlarına erişim ve Ilan domen nesnesine dönüştürme.
 */
final class IlanDepo
{
    /** @return Ilan[] */
    public function tumu(): array
    {
        $satirlar = Veritabani::pdo()->query('SELECT * FROM ilanlar ORDER BY id')->fetchAll();

        return array_map($this->canlandir(...), $satirlar);
    }

    public function idIle(int $id): ?Ilan
    {
        $s = Veritabani::pdo()->prepare('SELECT * FROM ilanlar WHERE id = ?');
        $s->execute([$id]);
        $satir = $s->fetch();

        return $satir ? $this->canlandir($satir) : null;
    }

    public function olustur(string $baslik, int $baslangicFiyati, int $saatlikDusus, int $rezervFiyat, DateTimeImmutable $baslangicZamani): int
    {
        $s = Veritabani::pdo()->prepare(
            'INSERT INTO ilanlar (baslik, baslangic_fiyati, saatlik_dusus, rezerv_fiyat, baslangic_zamani, olusturuldu)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $s->execute([
            $baslik,
            $baslangicFiyati,
            $saatlikDusus,
            $rezervFiyat,
            $baslangicZamani->format('c'),
            (new DateTimeImmutable())->format('c'),
        ]);

        return (int) Veritabani::pdo()->lastInsertId();
    }

    /** İlanın değişebilen durumunu (teklif sonrası) kaydeder. */
    public function guncelle(Ilan $ilan): void
    {
        $s = Veritabani::pdo()->prepare(
            'UPDATE ilanlar
             SET ilk_teklif_zamani = ?, bitis_zamani = ?, guncel_teklif = ?, son_teklif_sahibi = ?
             WHERE id = ?'
        );
        $s->execute([
            $ilan->ilkTeklifZamani()?->format('c'),
            $ilan->bitisZamani()?->format('c'),
            $ilan->guncelTeklifDegeri(),
            $ilan->sonTeklifSahibi(),
            $ilan->id,
        ]);
    }

    public function teklifKaydet(int $ilanId, int $kullaniciId, int $miktar, DateTimeImmutable $zaman): void
    {
        $s = Veritabani::pdo()->prepare(
            'INSERT INTO teklifler (ilan_id, kullanici_id, miktar, zaman) VALUES (?, ?, ?, ?)'
        );
        $s->execute([$ilanId, $kullaniciId, $miktar, $zaman->format('c')]);
    }

    /** @return list<array{ad: string, miktar: int, zaman: string}> */
    public function teklifGecmisi(int $ilanId): array
    {
        $s = Veritabani::pdo()->prepare(
            'SELECT k.ad AS ad, t.miktar AS miktar, t.zaman AS zaman
             FROM teklifler t JOIN kullanicilar k ON k.id = t.kullanici_id
             WHERE t.ilan_id = ? ORDER BY t.miktar DESC'
        );
        $s->execute([$ilanId]);

        return $s->fetchAll();
    }

    private function canlandir(array $r): Ilan
    {
        return Ilan::fromState([
            'id' => (int) $r['id'],
            'baslik' => $r['baslik'],
            'baslangicFiyati' => (int) $r['baslangic_fiyati'],
            'saatlikDusus' => (int) $r['saatlik_dusus'],
            'rezervFiyat' => (int) $r['rezerv_fiyat'],
            'baslangicZamani' => new DateTimeImmutable($r['baslangic_zamani']),
            'ilkTeklifZamani' => $r['ilk_teklif_zamani'] !== null ? new DateTimeImmutable($r['ilk_teklif_zamani']) : null,
            'bitisZamani' => $r['bitis_zamani'] !== null ? new DateTimeImmutable($r['bitis_zamani']) : null,
            'guncelTeklif' => $r['guncel_teklif'] !== null ? (int) $r['guncel_teklif'] : null,
            'sonTeklifSahibi' => $r['son_teklif_sahibi'],
        ]);
    }
}
