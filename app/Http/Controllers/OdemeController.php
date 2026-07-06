<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Durum;
use App\Models\Ilan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OdemeController extends Controller
{
    /** Kazanılan lot için ödeme sayfası (şimdilik yer tutucu). */
    public function goster(Request $request, Ilan $ilan): View
    {
        $kullanici = $request->user();

        // Yalnızca kazanan görebilir.
        abort_unless(
            $ilan->durum() === Durum::KAPANDI && $ilan->son_teklif_sahibi === $kullanici->name,
            403,
            'Bu ödeme sayfasına erişiminiz yok.'
        );

        return view('hesap.odeme', ['ilan' => $ilan]);
    }
}
