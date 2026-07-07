<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\Durum;
use App\Models\Ilan;
use Carbon\CarbonImmutable;
use Tests\TestCase;

/**
 * Yeni müzayede mantığı: sabit bitişli açık artırma; teklifsiz lot son 12 saatte düşer.
 */
class IlanTest extends TestCase
{
    private CarbonImmutable $t0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->t0 = CarbonImmutable::parse('2026-07-06 12:00:00');
    }

    /** Bitişi $saat sonra olan, teklifsiz bir lot. */
    private function ilan(int $bitisSaatSonra): Ilan
    {
        return new Ilan([
            'baslik' => 'Test Ürünü',
            'baslangic_fiyati' => 1000,
            'rezerv_fiyat' => 500,
            'bitis_zamani' => $this->t0->addHours($bitisSaatSonra),
        ]);
    }

    public function test_kapanisa_uzak_teklifsiz_lot_acik_artirmada_baslangic_fiyatinda(): void
    {
        $ilan = $this->ilan(24); // düşüş penceresine (12s) daha girmedi
        $this->assertSame(Durum::ACIK_ARTIRMA, $ilan->durum($this->t0));
        $this->assertSame(1000, $ilan->guncelFiyat($this->t0));
        $this->assertSame(1000, $ilan->minTeklif($this->t0));
    }

    public function test_son_12_saatte_dusuyor_ve_baslangicta_fiyat_tam(): void
    {
        $ilan = $this->ilan(12); // düşüş tam şimdi başlıyor
        $this->assertSame(Durum::DUSUYOR, $ilan->durum($this->t0));
        $this->assertSame(1000, $ilan->dusenFiyat($this->t0));
    }

    public function test_fiyat_lineer_duser(): void
    {
        // Bitiş 12 saat sonra; 6 saat geçince yarı yol: 1000 -> 750
        $this->assertSame(750, $this->ilan(12)->dusenFiyat($this->t0->addHours(6)));
    }

    public function test_rezerv_tabaninin_altina_inmez(): void
    {
        $this->assertSame(500, $this->ilan(12)->dusenFiyat($this->t0->addHours(20)));
    }

    public function test_artirim_tablosu(): void
    {
        $this->assertSame(50, Ilan::artirimAdimi(700));
        $this->assertSame(100, Ilan::artirimAdimi(1000));
        $this->assertSame(250, Ilan::artirimAdimi(5000));
    }

    public function test_dusuyorken_min_teklif_dusen_fiyattir(): void
    {
        $this->assertSame(750, $this->ilan(12)->minTeklif($this->t0->addHours(6)));
    }
}
