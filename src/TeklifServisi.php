<?php

declare(strict_types=1);

namespace App;

use App\Cekirdek\Veritabani;
use App\Depo\IlanDepo;
use DateTimeImmutable;
use DomainException;

/**
 * Teklif verme akışını yönetir: domen kuralını uygular ve kalıcılaştırır.
 */
final class TeklifServisi
{
    public function __construct(
        private readonly IlanDepo $ilanDepo = new IlanDepo(),
    ) {
    }

    /**
     * @throws DomainException Teklif geçersizse (domen kuralı) veya ilan yoksa.
     */
    public function teklifVer(int $ilanId, int $kullaniciId, string $kullaniciAdi, int $miktar): Ilan
    {
        $pdo = Veritabani::pdo();
        $pdo->beginTransaction();
        try {
            $ilan = $this->ilanDepo->idIle($ilanId);
            if ($ilan === null) {
                throw new DomainException('İlan bulunamadı.');
            }

            $now = new DateTimeImmutable();
            $ilan->teklifVer($kullaniciAdi, $miktar, $now); // geçersizse fırlatır

            $this->ilanDepo->guncelle($ilan);
            $this->ilanDepo->teklifKaydet($ilanId, $kullaniciId, $miktar, $now);

            $pdo->commit();

            return $ilan;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
