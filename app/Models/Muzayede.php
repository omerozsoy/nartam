<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bir müzayede (satış) — numaralı, isimli, başlangıç/bitişli. Lotlar buna bağlanır.
 * Kademeli kapanış: ilk lot {@see $bitis}'te; sonraki lotlar aralık1/aralık2 ile.
 */
class Muzayede extends Model
{
    protected $table = 'muzayedeler';

    protected $fillable = [
        'no', 'ad', 'baslangic', 'bitis', 'esik_lot', 'aralik1', 'aralik2', 'aktif',
    ];

    protected function casts(): array
    {
        return [
            'baslangic' => 'immutable_datetime',
            'bitis' => 'immutable_datetime',
            'esik_lot' => 'integer',
            'aralik1' => 'integer',
            'aralik2' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function ilanlar(): HasMany
    {
        return $this->hasMany(Ilan::class);
    }

    /** Teklifler açıldı mı? (başlangıç geçti mi) */
    public function basladi(?CarbonImmutable $now = null): bool
    {
        return ($now ?? CarbonImmutable::now())->greaterThanOrEqualTo($this->baslangic);
    }

    /** Sitede gösterilen aktif müzayede (yoksa null). */
    public static function aktif(): ?self
    {
        return static::where('aktif', true)->latest('id')->first();
    }
}
