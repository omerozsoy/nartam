<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\Durum;
use App\Models\Ilan;
use Carbon\CarbonImmutable;
use Tests\TestCase;

/**
 * İki fazlı müzayede mantığının testleri (model metodları; veritabanı sorgusu yok).
 */
class IlanTest extends TestCase
{
    private CarbonImmutable $t0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->t0 = CarbonImmutable::parse('2026-07-06 12:00:00');
    }

    private function ilan(): Ilan
    {
        return new Ilan([
            'baslik' => 'Test Ürünü',
            'baslangic_fiyati' => 1000,
            'saatlik_dusus' => 100,
            'rezerv_fiyat' => 500,
            'baslangic_zamani' => $this->t0,
        ]);
    }

    public function test_baslangicta_durum_dusuyor_ve_fiyat_tam(): void
    {
        $ilan = $this->ilan();
        $this->assertSame(Durum::DUSUYOR, $ilan->durum($this->t0));
        $this->assertSame(1000, $ilan->dusenFiyat($this->t0));
    }

    public function test_fiyat_saatlik_duser(): void
    {
        $this->assertSame(700, $this->ilan()->dusenFiyat($this->t0->addMinutes(210))); // 3.5 saat -> 3 tam saat
    }

    public function test_rezerv_tabaninin_altina_inmez(): void
    {
        $this->assertSame(500, $this->ilan()->dusenFiyat($this->t0->addHours(50)));
    }

    public function test_artirim_tablosu(): void
    {
        $this->assertSame(50, Ilan::artirimAdimi(700));
        $this->assertSame(100, Ilan::artirimAdimi(1000));
        $this->assertSame(250, Ilan::artirimAdimi(5000));
    }

    public function test_dusuyorken_min_teklif_dusen_fiyattir(): void
    {
        $an = $this->t0->addHours(3); // fiyat 700
        $this->assertSame(700, $this->ilan()->minTeklif($an));
    }
}
