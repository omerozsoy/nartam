<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeyAdimi extends Model
{
    protected $table = 'pey_adimlari';

    protected $fillable = ['alt_sinir', 'ust_sinir', 'adim'];

    protected function casts(): array
    {
        return [
            'alt_sinir' => 'integer',
            'ust_sinir' => 'integer',
            'adim' => 'integer',
        ];
    }
}
