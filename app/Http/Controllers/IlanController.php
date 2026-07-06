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
        return view('ilanlar.liste', ['ilanlar' => $this->siraliOzetler()]);
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

        return Ilan::orderBy('id')->get()
            ->map(fn (Ilan $i) => Sunum::ilan($i))
            ->sortBy(fn (array $o) => sprintf('%d-%08d', $oncelik[$o['durum']] ?? 9, $o['id']))
            ->values();
    }
}
