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
        'dusus_periyodu',
        'rezerv_fiyat',
        'baslangic_zamani',
        'ilk_teklif_zamani',
        'bitis_zamani',
        'guncel_teklif',
        'son_teklif_sahibi',
        'lider_id',
        'lider_max',
        'bildirildi',
        'ithal_kodu',
    ];

    protected function casts(): array
    {
        return [
            'baslangic_zamani' => 'immutable_datetime',
            'ilk_teklif_zamani' => 'immutable_datetime',
            'bitis_zamani' => 'immutable_datetime',
            'baslangic_fiyati' => 'integer',
            'saatlik_dusus' => 'integer',
            'dusus_periyodu' => 'integer',
            'rezerv_fiyat' => 'integer',
            'guncel_teklif' => 'integer',
            'lider_id' => 'integer',
            'lider_max' => 'integer',
            'bildirildi' => 'boolean',
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

    /** Düşüş periyodu (saniye). 1=saniye, 60=dakika, 3600=saat. */
    public function periyot(): int
    {
        return $this->dusus_periyodu ?: 3600;
    }

    /** Düşüş fazındaki anlık fiyat (rezervde taban yapar). */
    public function dusenFiyat(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();
        $gecen = intdiv(max(0, $now->getTimestamp() - $this->baslangic_zamani->getTimestamp()), $this->periyot());
        $fiyat = $this->baslangic_fiyati - ($gecen * $this->saatlik_dusus);

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

    /** @var list<array{alt: int, adim: int}>|null İstek-içi önbellek. */
    private static ?array $peyAdimCache = null;

    /** Kademeli artırım tutarı — yönetimdeki pey adım tablosundan okur. */
    public static function artirimAdimi(int $fiyat): int
    {
        foreach (self::peyAdimlari() as $kademe) {
            if ($fiyat >= $kademe['alt']) {
                return $kademe['adim'];
            }
        }

        return 50;
    }

    /** @return list<array{alt: int, adim: int}> Pey kademeleri (alt'a göre azalan) — ön yüz için. */
    public static function peyKademeleri(): array
    {
        return self::peyAdimlari();
    }

    /** @return list<array{alt: int, adim: int}> alt_sinir'e göre AZALAN sıralı */
    private static function peyAdimlari(): array
    {
        if (self::$peyAdimCache !== null) {
            return self::$peyAdimCache;
        }

        try {
            self::$peyAdimCache = PeyAdimi::orderByDesc('alt_sinir')->get()
                ->map(fn (PeyAdimi $p) => ['alt' => $p->alt_sinir, 'adim' => $p->adim])
                ->all();
        } catch (\Throwable) {
            self::$peyAdimCache = [];
        }

        if (self::$peyAdimCache === []) {
            self::$peyAdimCache = [
                ['alt' => 5000, 'adim' => 250],
                ['alt' => 1000, 'adim' => 100],
                ['alt' => 0, 'adim' => 50],
            ];
        }

        return self::$peyAdimCache;
    }

    /** Düşüş fazında bir sonraki fiyat düşüşünün zamanı (taban değilse). */
    public function sonrakiDususZamani(?CarbonImmutable $now = null): ?CarbonImmutable
    {
        $now ??= CarbonImmutable::now();

        if ($this->durum($now) !== Durum::DUSUYOR || $this->dusenFiyat($now) <= $this->rezerv_fiyat) {
            return null;
        }

        $gecen = intdiv(max(0, $now->getTimestamp() - $this->baslangic_zamani->getTimestamp()), $this->periyot());

        return $this->baslangic_zamani->addSeconds(($gecen + 1) * $this->periyot());
    }
}
