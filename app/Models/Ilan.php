<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Durum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Müzayede lotu. Klasik (yükselen) açık artırma:
 *   - Her lotun sabit bir {@see $bitis_zamani} kapanış anı vardır (kademeli kapanış).
 *   - Herkes yukarı doğru teklif verir; bitişte en yüksek teklif kazanır.
 *   - Tek istisna: HİÇ teklif almamış lot, bitişe {@see DUSUS_PENCERESI} (son 12 saat)
 *     kala başlangıç fiyatından {@see $rezerv_fiyat} tabanına doğru düşmeye başlar.
 *     İlk teklif gelince o anki düşmüş fiyattan normal açık artırma sürer (bitiş sabit).
 */
class Ilan extends Model
{
    /** Teklifsiz lotun fiyatının düşmeye başladığı pencere: bitişten önceki son 12 saat (saniye). */
    public const DUSUS_PENCERESI = 12 * 60 * 60;

    /** Düşüş adım periyodu (saniye) — fiyat bu aralıklarla kademeli iner. */
    public const DUSUS_ADIM_SN = 10 * 60;

    /** Bu süreden az kala gelen teklif kapanışı uzatır (saniye). */
    public const ANTI_SNIPE_ESIK = 2 * 60;

    /** Anti-snipe tetiklenince kapanış bu kadara çekilir (saniye). */
    public const ANTI_SNIPE_UZATMA = 2 * 60;

    protected $table = 'ilanlar';

    protected $fillable = [
        'muzayede_id',
        'baslik',
        'lot_no',
        'gorsel_url',
        'carusel',
        'carusel_sira',
        'carusel_konum',
        'carusel_arka',
        'coverflow',
        'coverflow_sira',
        'alt_baslik',
        'kategori',
        'aciklama',
        'baslangic_fiyati',
        'rezerv_fiyat',
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
            'bitis_zamani' => 'immutable_datetime',
            'baslangic_fiyati' => 'integer',
            'rezerv_fiyat' => 'integer',
            'guncel_teklif' => 'integer',
            'lider_id' => 'integer',
            'lider_max' => 'integer',
            'bildirildi' => 'boolean',
            'carusel' => 'boolean',
            'coverflow' => 'boolean',
        ];
    }

    public function teklifler(): HasMany
    {
        return $this->hasMany(Teklif::class);
    }

    public function muzayede(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Muzayede::class);
    }

    /** En az bir teklif almış mı? */
    public function teklifAldi(): bool
    {
        return $this->lider_id !== null;
    }

    public function durum(?CarbonImmutable $now = null): Durum
    {
        $now ??= CarbonImmutable::now();

        // Bağlı olduğu müzayede henüz başlamadıysa teklif kapalı (Yakında).
        if ($this->muzayede && ! $this->muzayede->basladi($now)) {
            return Durum::YAKINDA;
        }

        // Programlanmamış (bitişi olmayan) lot: açık kabul, düşmez, kapanmaz.
        if ($this->bitis_zamani === null) {
            return Durum::ACIK_ARTIRMA;
        }

        if ($now->greaterThanOrEqualTo($this->bitis_zamani)) {
            return Durum::KAPANDI;
        }

        // Teklif geldiyse normal açık artırma. Teklif yoksa ve son 12 saatteysek fiyat düşüyor.
        if (! $this->teklifAldi() && $now->getTimestamp() >= $this->dususBaslangici()) {
            return Durum::DUSUYOR;
        }

        return Durum::ACIK_ARTIRMA;
    }

    /** Düşüşün başladığı an (timestamp): bitiş − 12 saat. */
    private function dususBaslangici(): int
    {
        return $this->bitis_zamani->getTimestamp() - self::DUSUS_PENCERESI;
    }

    /** Düşüş fazındaki anlık fiyat (başlangıçtan rezerve, kademeli; rezervde taban yapar). */
    public function dusenFiyat(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        if ($this->bitis_zamani === null) {
            return (int) $this->baslangic_fiyati;
        }

        $gecenSn = $now->getTimestamp() - $this->dususBaslangici();
        if ($gecenSn <= 0) {
            return (int) $this->baslangic_fiyati;
        }

        $toplamAdim = (int) (self::DUSUS_PENCERESI / self::DUSUS_ADIM_SN);
        $gecenAdim = min($toplamAdim, intdiv($gecenSn, self::DUSUS_ADIM_SN));

        $aralik = (int) $this->baslangic_fiyati - (int) $this->rezerv_fiyat;
        $dusus = (int) round($aralik * $gecenAdim / $toplamAdim);

        return max((int) $this->rezerv_fiyat, (int) $this->baslangic_fiyati - $dusus);
    }

    /** Ekranda gösterilecek geçerli fiyat (faza göre). */
    public function guncelFiyat(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        if ($this->durum($now) === Durum::DUSUYOR) {
            return $this->dusenFiyat($now);
        }

        // Teklif varsa güncel teklif; yoksa başlangıç fiyatı.
        return $this->teklifAldi() ? (int) $this->guncel_teklif : (int) $this->baslangic_fiyati;
    }

    /** Bu an için geçerli en düşük kabul edilebilir teklif. */
    public function minTeklif(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();
        $durum = $this->durum($now);

        if ($durum === Durum::KAPANDI) {
            return $this->guncelFiyat($now);
        }

        if ($durum === Durum::DUSUYOR) {
            return $this->dusenFiyat($now);
        }

        // Açık artırma: teklif varsa bir pey adımı üstü; yoksa başlangıç fiyatı.
        if (! $this->teklifAldi()) {
            return (int) $this->baslangic_fiyati;
        }

        return (int) $this->guncel_teklif + self::artirimAdimi((int) $this->guncel_teklif);
    }

    /** @var list<array{alt: int, ust: int|null, adim: int}>|null İstek-içi önbellek. */
    private static ?array $peyAdimCache = null;

    /** Kademeli artırım tutarı — yönetimdeki pey adım tablosundan okur. */
    public static function artirimAdimi(int $fiyat): int
    {
        foreach (self::peyAdimlari() as $k) {
            if ($fiyat >= $k['alt'] && ($k['ust'] === null || $fiyat <= $k['ust'])) {
                return $k['adim'];
            }
        }

        return 50;
    }

    /** @return list<array{alt: int, ust: int|null, adim: int}> Pey kademeleri — ön yüz için. */
    public static function peyKademeleri(): array
    {
        return self::peyAdimlari();
    }

    /** @return list<array{alt: int, ust: int|null, adim: int}> alt_sinir'e göre AZALAN sıralı */
    private static function peyAdimlari(): array
    {
        if (self::$peyAdimCache !== null) {
            return self::$peyAdimCache;
        }

        try {
            self::$peyAdimCache = PeyAdimi::orderByDesc('alt_sinir')->get()
                ->map(fn (PeyAdimi $p) => ['alt' => $p->alt_sinir, 'ust' => $p->ust_sinir, 'adim' => $p->adim])
                ->all();
        } catch (\Throwable) {
            self::$peyAdimCache = [];
        }

        if (self::$peyAdimCache === []) {
            self::$peyAdimCache = [
                ['alt' => 5000, 'ust' => null, 'adim' => 250],
                ['alt' => 1000, 'ust' => 4999, 'adim' => 100],
                ['alt' => 0, 'ust' => 999, 'adim' => 50],
            ];
        }

        return self::$peyAdimCache;
    }

    /** Düşüş fazında bir sonraki fiyat düşüşünün zamanı (taban değilse). */
    public function sonrakiDususZamani(?CarbonImmutable $now = null): ?CarbonImmutable
    {
        $now ??= CarbonImmutable::now();

        if ($this->durum($now) !== Durum::DUSUYOR || $this->dusenFiyat($now) <= (int) $this->rezerv_fiyat) {
            return null;
        }

        $gecenAdim = intdiv($now->getTimestamp() - $this->dususBaslangici(), self::DUSUS_ADIM_SN);
        $sonraki = $this->dususBaslangici() + ($gecenAdim + 1) * self::DUSUS_ADIM_SN;

        return CarbonImmutable::createFromTimestamp(min($sonraki, $this->bitis_zamani->getTimestamp()));
    }
}
