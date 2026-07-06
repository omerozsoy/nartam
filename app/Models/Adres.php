<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adres extends Model
{
    protected $table = 'adresler';

    protected $fillable = [
        'user_id',
        'tur',
        'ad_soyad',
        'telefon',
        'il',
        'ilce',
        'adres',
        'posta_kodu',
    ];

    public function turEtiket(): string
    {
        return $this->tur === 'fatura' ? 'Fatura Adresi' : 'Teslimat Adresi';
    }

    public function kullanici(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
