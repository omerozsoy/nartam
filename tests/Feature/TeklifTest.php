<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Durum;
use App\Models\Ilan;
use App\Models\User;
use App\Services\TeklifServisi;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TeklifTest extends TestCase
{
    use RefreshDatabase;

    private function ilan(): Ilan
    {
        return Ilan::create([
            'baslik' => 'Test',
            'baslangic_fiyati' => 1000,
            'saatlik_dusus' => 100,
            'rezerv_fiyat' => 500,
            'baslangic_zamani' => CarbonImmutable::now()->subHours(3), // fiyat 700
        ]);
    }

    private function kullanici(string $ad = 'Ali'): User
    {
        return User::factory()->create(['name' => $ad]);
    }

    public function test_ilk_teklif_acik_artirmaya_gecirir(): void
    {
        $ilan = $this->ilan();
        (new TeklifServisi())->teklifVer($ilan, $this->kullanici(), 700);

        $this->assertSame(Durum::ACIK_ARTIRMA, $ilan->fresh()->durum());
        $this->assertSame(700, $ilan->fresh()->guncel_teklif);
        $this->assertDatabaseCount('teklifler', 1);
    }

    public function test_dusen_fiyatin_altindaki_teklif_reddedilir(): void
    {
        $this->expectException(ValidationException::class);
        (new TeklifServisi())->teklifVer($this->ilan(), $this->kullanici(), 699);
    }

    public function test_artirim_adiminin_altindaki_teklif_reddedilir(): void
    {
        $ilan = $this->ilan();
        $servis = new TeklifServisi();
        $servis->teklifVer($ilan, $this->kullanici('Ali'), 700); // min sonraki: 750

        $this->expectException(ValidationException::class);
        $servis->teklifVer($ilan->fresh(), $this->kullanici('Veli'), 720);
    }

    public function test_anti_snipe_sayaci_uzatir(): void
    {
        $ilan = $this->ilan();
        $servis = new TeklifServisi();
        $servis->teklifVer($ilan, $this->kullanici('Ali'), 700);

        $ilan = $ilan->fresh();
        $eskiBitis = $ilan->bitis_zamani;

        // Bitişe 1 dk kala ol
        CarbonImmutable::setTestNow($eskiBitis->subMinute());
        $servis->teklifVer($ilan, $this->kullanici('Veli'), 750);
        $yeniBitis = $ilan->fresh()->bitis_zamani;

        $this->assertTrue($yeniBitis->greaterThan($eskiBitis), 'Sayaç uzamalı');
        CarbonImmutable::setTestNow();
    }
}
