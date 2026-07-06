<?php

declare(strict_types=1);

namespace App;

/**
 * Bir müzayede oturumu — içinde satışa çıkan kalemleri (lot) tutar.
 */
final class Muzayede
{
    /** @var Kalem[] */
    private array $kalemler = [];

    public function __construct(
        public readonly string $baslik,
    ) {
    }

    public function kalemEkle(Kalem $kalem): void
    {
        $this->kalemler[] = $kalem;
    }

    /** @return Kalem[] */
    public function kalemler(): array
    {
        return $this->kalemler;
    }
}
