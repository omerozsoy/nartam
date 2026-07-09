<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Durum;
use App\Models\Ilan;
use App\Models\User;
use App\Support\Sunum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HesapController extends Controller
{
    /** Kullanıcının pey verdiği (teklif verdiği) eserler. */
    public function index(Request $request): View
    {
        $satirlar = $this->satirlar($request->user());

        $takip = $request->user()->takipler()->with('muzayede')->withCount('teklifler')->orderByPivot('created_at', 'desc')->get()
            ->map(fn (Ilan $i) => Sunum::ilan($i, null, $request->user()->id, false, null, true));

        return view('hesap.index', [
            'kazandiklarim' => $satirlar->where('durumum', 'kazandi')->values(),
            'diger' => $satirlar->where('durumum', '!=', 'kazandi')->values(),
            'takipEttiklerim' => $takip,
        ]);
    }

    /** Canlı güncelleme için JSON. */
    public function api(Request $request): JsonResponse
    {
        return response()->json($this->satirlar($request->user()));
    }

    /** @return Collection<int, array> */
    private function satirlar(User $kullanici): Collection
    {
        $ilanIdleri = $kullanici->teklifler()->pluck('ilan_id')->unique();

        return Ilan::whereIn('id', $ilanIdleri)
            ->withCount('teklifler')
            ->with('muzayede')
            ->get()
            ->map(function (Ilan $ilan) use ($kullanici) {
                $ozet = Sunum::ilan($ilan);
                $durum = $ilan->durum();
                $onde = $ilan->son_teklif_sahibi === $kullanici->name;

                $ozet['muzayedeId'] = $ilan->muzayede_id;
                $ozet['muzayedeBaslik'] = $ilan->muzayede
                    ? $ilan->muzayede->no . '. Müzayede · ' . $ilan->muzayede->ad
                    : 'Müzayede';

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
            ->sortBy(fn (array $o) => sprintf('%d-%012d', $o['durum'] === 'kapandi' ? 1 : 0, PHP_INT_MAX - $o['benimTeklifim']))
            ->values();
    }
}
