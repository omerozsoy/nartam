<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Durum;
use App\Models\Ilan;
use App\Support\Sunum;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HesapController extends Controller
{
    /** Kullanıcının pey verdiği (teklif verdiği) eserler. */
    public function index(Request $request): View
    {
        $kullanici = $request->user();

        $ilanIdleri = $kullanici->teklifler()->pluck('ilan_id')->unique();

        $satirlar = Ilan::whereIn('id', $ilanIdleri)
            ->withCount('teklifler')
            ->get()
            ->map(function (Ilan $ilan) use ($kullanici) {
                $ozet = Sunum::ilan($ilan);
                $durum = $ilan->durum();
                $onde = $ilan->son_teklif_sahibi === $kullanici->name;

                $benim = (int) $kullanici->teklifler()->where('ilan_id', $ilan->id)->max('miktar');
                $ozet['benimTeklifim'] = $benim;
                $ozet['benimTeklifimBicim'] = number_format($benim, 0, ',', '.') . ' ₺';

                $ozet['durumum'] = match (true) {
                    $durum === Durum::KAPANDI && $onde => 'kazandi',
                    $durum === Durum::KAPANDI => 'kaybetti',
                    $onde => 'onde',
                    default => 'geride',
                };

                return $ozet;
            })
            // Açık artırmadakiler üstte, kapananlar altta; sonra teklifime göre
            ->sortBy(fn (array $o) => sprintf('%d-%012d', $o['durum'] === 'kapandi' ? 1 : 0, PHP_INT_MAX - $o['benimTeklifim']))
            ->values();

        return view('hesap.index', ['satirlar' => $satirlar]);
    }
}
