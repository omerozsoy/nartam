<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ilan;
use App\Models\Teklif;
use App\Support\Sunum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class IlanController extends Controller
{
    public function index(): View
    {
        return view('ilanlar.liste', ['gruplar' => $this->siraliOzetler()->groupBy('durum')]);
    }

    /** Açık Artırma — yalnızca yükselen açık artırmalar. */
    public function acikArtirma(): View
    {
        return view('ilanlar.tekbolum', [
            'baslik' => 'Açık Artırma',
            'aciklama' => 'İlk teklifle başlayan, 24 saat süren yükselen açık artırmalar',
            'ilanlar' => $this->siraliOzetler()->where('durum', 'acik_artirma')->values(),
        ]);
    }

    /** Açık Eksiltme — fiyatı düşen (düşen fiyat) ürünler. */
    public function acikEksiltme(): View
    {
        return view('ilanlar.tekbolum', [
            'baslik' => 'Açık Eksiltme',
            'aciklama' => 'Teklif gelene kadar fiyat düşer; ilk teklifle açık artırma başlar',
            'ilanlar' => $this->siraliOzetler()->where('durum', 'dusuyor')->values(),
        ]);
    }

    /** Tekil lot (detay) sayfası. */
    public function goster(Ilan $ilan): View
    {
        $ilan->loadCount('teklifler');
        $teklifler = $ilan->teklifler()->with('kullanici')->orderByDesc('miktar')->take(20)->get();

        $benimId = Auth::id();
        $benimMax = $benimId ? $ilan->teklifler()->where('kullanici_id', $benimId)->max('miktar') : null;

        return view('ilanlar.detay', [
            'ilan' => $ilan,
            'ozet' => Sunum::ilan($ilan, null, $benimId, $benimMax !== null, $benimMax !== null ? (int) $benimMax : null),
            'teklifler' => $teklifler,
        ]);
    }

    /** Canlı güncelleme (polling) için JSON. */
    public function api(): JsonResponse
    {
        return response()->json($this->siraliOzetler());
    }

    /**
     * İlanları duruma göre sıralar: açık artırmalar üstte, düşen fiyatlar altta,
     * kapananlar en sonda. Aynı grup içinde id'ye göre.
     */
    private function siraliOzetler(): Collection
    {
        $oncelik = ['acik_artirma' => 0, 'dusuyor' => 1, 'kapandi' => 2];
        $benimId = Auth::id();
        $benimMaxlar = $benimId
            ? Teklif::where('kullanici_id', $benimId)
                ->selectRaw('ilan_id, MAX(miktar) as maks')
                ->groupBy('ilan_id')
                ->pluck('maks', 'ilan_id')
            : collect();

        return Ilan::withCount('teklifler')->orderBy('id')->get()
            ->map(function (Ilan $i) use ($benimId, $benimMaxlar) {
                $m = $benimMaxlar->get($i->id);

                return Sunum::ilan($i, null, $benimId, $m !== null, $m !== null ? (int) $m : null);
            })
            // Grup önceliği; grup içinde: lot no'su olanlar (açık artırma) lot no'ya göre 1,2,3…
            ->sortBy(fn (array $o) => sprintf('%d-%08d', $oncelik[$o['durum']] ?? 9, $o['lotNo'] ?? $o['id']))
            ->values();
    }
}
