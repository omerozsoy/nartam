<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ilan;
use App\Support\Sunum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class IlanController extends Controller
{
    public function index(): View
    {
        return view('ilanlar.liste', ['gruplar' => $this->siraliOzetler()->groupBy('durum')]);
    }

    /** Tekil lot (detay) sayfası. */
    public function goster(Ilan $ilan): View
    {
        $ilan->loadCount('teklifler');
        $teklifler = $ilan->teklifler()->with('kullanici')->orderByDesc('miktar')->take(20)->get();

        return view('ilanlar.detay', [
            'ilan' => $ilan,
            'ozet' => Sunum::ilan($ilan),
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

        return Ilan::withCount('teklifler')->orderBy('id')->get()
            ->map(fn (Ilan $i) => Sunum::ilan($i))
            // Grup önceliği; grup içinde: lot no'su olanlar (açık artırma) lot no'ya göre 1,2,3…
            ->sortBy(fn (array $o) => sprintf('%d-%08d', $oncelik[$o['durum']] ?? 9, $o['lotNo'] ?? $o['id']))
            ->values();
    }
}
