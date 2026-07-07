<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ayar;
use Illuminate\View\View;

class SayfaController extends Controller
{
    /** İletişim bilgilerinde düzenlenebilen tüm alanlar. */
    public const ILETISIM_ALANLARI = [
        'iletisim_adres',
        'iletisim_telefon',
        'iletisim_eposta',
        'iletisim_saatler',
        'iletisim_metin',
        'iletisim_harita',
        'sosyal_instagram',
        'sosyal_facebook',
        'sosyal_twitter',
        'sosyal_whatsapp',
    ];

    public function iletisim(): View
    {
        return view('sayfa.iletisim', [
            'a' => Ayar::coklu(self::ILETISIM_ALANLARI),
        ]);
    }

    public function ekspertiz(): View
    {
        return view('sayfa.ekspertiz', [
            'baslik' => Ayar::oku('ekspertiz_baslik', 'Ekspertiz'),
            'icerik' => Ayar::oku('ekspertiz_metin'),
        ]);
    }
}
