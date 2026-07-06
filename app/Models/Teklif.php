<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teklif extends Model
{
    protected $table = 'teklifler';

    protected $fillable = [
        'ilan_id',
        'kullanici_id',
        'miktar',
        'zaman',
    ];

    protected function casts(): array
    {
        return [
            'miktar' => 'integer',
            'zaman' => 'immutable_datetime',
        ];
    }

    public function ilan(): BelongsTo
    {
        return $this->belongsTo(Ilan::class);
    }

    public function kullanici(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kullanici_id');
    }
}
