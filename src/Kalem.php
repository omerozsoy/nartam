<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;

/**
 * Müzayedede satışa çıkan tek bir kalem (lot).
 * Her kalemin bir bitiş zamanı vardır; sayaç bu zamana göre geri sayar.
 */
final class Kalem
{
    public function __construct(
        public readonly int $id,
        public readonly string $ad,
        public readonly int $baslangicFiyati,
        public readonly DateTimeImmutable $bitisZamani,
    ) {
    }

    /** Sayacın JS tarafında kullanması için bitiş zamanının unix timestamp'i. */
    public function bitisTimestamp(): int
    {
        return $this->bitisZamani->getTimestamp();
    }

    public function fiyatBicimli(): string
    {
        return number_format($this->baslangicFiyati, 0, ',', '.') . ' ₺';
    }
}
