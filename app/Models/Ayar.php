<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Basit key-value ayar deposu (iletişim bilgileri, sayfa metinleri vb.).
 */
class Ayar extends Model
{
    protected $table = 'ayarlar';

    protected $fillable = ['anahtar', 'deger'];

    /** Tek bir ayarı okur. */
    public static function oku(string $anahtar, ?string $varsayilan = null): ?string
    {
        return static::query()->where('anahtar', $anahtar)->value('deger') ?? $varsayilan;
    }

    /** Birden çok ayarı [anahtar => deger] dizisi olarak okur. */
    public static function coklu(array $anahtarlar): array
    {
        $mevcut = static::query()->whereIn('anahtar', $anahtarlar)->pluck('deger', 'anahtar')->toArray();

        $sonuc = [];
        foreach ($anahtarlar as $anahtar) {
            $sonuc[$anahtar] = $mevcut[$anahtar] ?? null;
        }

        return $sonuc;
    }

    /** [anahtar => deger] dizisini toplu kaydeder. */
    public static function kaydet(array $veriler): void
    {
        foreach ($veriler as $anahtar => $deger) {
            static::updateOrCreate(['anahtar' => $anahtar], ['deger' => $deger]);
        }
    }
}
