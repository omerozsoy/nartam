<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Durum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Müzayede ilanı. İki fazlı çalışır:
 *   1) DÜŞEN FİYAT (Dutch): teklif gelene kadar fiyat her saat {@see $saatlik_dusus}
 *      kadar düşer, {@see $rezerv_fiyat} tabanında durur.
 *   2) AÇIK ARTIRMA (English): ilk teklifle o anki düşmüş fiyat taban olur ve 24 saatlik
 *      geri sayım başlar. Kademeli artırım + son 2 dk'da anti-snipe uzatması.
 */
class Ilan extends Model
{
    /** Açık artırma fazının süresi (saniye). */
    public const ACIK_ARTIRMA_SURESI = 24 * 60 * 60;

    /** Bu süreden az kala gelen teklif sayacı uzatır (saniye). */
    public const ANTI_SNIPE_ESIK = 2 * 60;

    /** Anti-snipe tetiklenince sayaç bu kadara çekilir (saniye). */
    public const ANTI_SNIPE_UZATMA = 2 * 60;

    protected $table = 'ilanlar';

    protected $fillable = [
        'baslik',
        'lot_no',
        'gorsel_url',
        'alt_baslik',
        'aciklama',
        'baslangic_fiyati',
        'saatlik_dusus',
        'rezerv_fiyat',
        'baslangic_zamani',
        'ilk_teklif_zamani',
        'bitis_zamani',
        'guncel_teklif',
        'son_teklif_sahibi',
    ];

    protected function casts(): array
    {
        return [
            'baslangic_zamani' => 'immutable_datetime',
            'ilk_teklif_zamani' => 'immutable_datetime',
            'bitis_zamani' => 'immutable_datetime',
            'baslangic_fiyati' => 'integer',
            'saatlik_dusus' => 'integer',
            'rezerv_fiyat' => 'integer',
            'guncel_teklif' => 'integer',
        ];
    }

    public function teklifler(): HasMany
    {
        return $this->hasMany(Teklif::class);
    }

    public function durum(?CarbonImmutable $now = null): Durum
    {
        $now ??= CarbonImmutable::now();

        if ($this->ilk_teklif_zamani === null) {
            return Durum::DUSUYOR;
        }

        return $now->greaterThanOrEqualTo($this->bitis_zamani) ? Durum::KAPANDI : Durum::ACIK_ARTIRMA;
    }

    /** Düşüş fazındaki anlık fiyat (rezervde taban yapar). */
    public function dusenFiyat(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();
        $gecenSaat = intdiv(max(0, $now->getTimestamp() - $this->baslangic_zamani->getTimestamp()), 3600);
        $fiyat = $this->baslangic_fiyati - ($gecenSaat * $this->saatlik_dusus);

        return max($this->rezerv_fiyat, $fiyat);
    }

    /** Ekranda gösterilecek geçerli fiyat (faza göre). */
    public function guncelFiyat(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        return $this->durum($now) === Durum::DUSUYOR
            ? $this->dusenFiyat($now)
            : (int) $this->guncel_teklif;
    }

    /** Bu an için geçerli en düşük kabul edilebilir teklif. */
    public function minTeklif(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        if ($this->durum($now) === Durum::DUSUYOR) {
            return $this->dusenFiyat($now);
        }

        return (int) $this->guncel_teklif + self::artirimAdimi((int) $this->guncel_teklif);
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

    /** Düşüş fazında bir sonraki fiyat düşüşünün zamanı (taban değilse). */
    public function sonrakiDususZamani(?CarbonImmutable $now = null): ?CarbonImmutable
    {
        $now ??= CarbonImmutable::now();

        if ($this->durum($now) !== Durum::DUSUYOR || $this->dusenFiyat($now) <= $this->rezerv_fiyat) {
            return null;
        }

        $gecenSaat = intdiv(max(0, $now->getTimestamp() - $this->baslangic_zamani->getTimestamp()), 3600);

        return $this->baslangic_zamani->addHours($gecenSaat + 1);
    }
}
