<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ilan;
use App\Models\Teklif;
use App\Support\Sunum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    /** Tekil lot (detay) sayfası. */
    public function goster(Ilan $ilan): View
    {
        $ilan->loadCount('teklifler');
        $ilan->load('muzayede');
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

    /** Otomatik tamamlama araması: sanatçı (başlık), eser (alt başlık) veya lot no. */
    public function ara(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';

        $ilanlar = Ilan::query()
            ->where(function ($w) use ($like, $q) {
                $w->where('baslik', 'like', $like)
                    ->orWhere('alt_baslik', 'like', $like);
                if (ctype_digit($q)) {
                    $w->orWhere('lot_no', (int) $q);
                }
            })
            ->orderByRaw('lot_no is null')
            ->orderBy('lot_no')
            ->orderBy('id')
            ->limit(8)
            ->get();

        return response()->json($ilanlar->map(fn (Ilan $i) => [
            'id' => $i->id,
            'baslik' => $i->baslik,
            'altBaslik' => $i->alt_baslik,
            'lotNo' => $i->lot_no,
            'gorselUrl' => $i->gorsel_url,
            'url' => route('ilan.goster', $i->id),
        ]));
    }

    /**
     * İlanları duruma göre sıralar: açık artırmalar üstte, düşen fiyatlar altta,
     * kapananlar en sonda. Aynı grup içinde id'ye göre.
     */
    private function siraliOzetler(): Collection
    {
        $oncelik = ['acik_artirma' => 0, 'dusuyor' => 1, 'yakinda' => 2, 'kapandi' => 3];
        $benimId = Auth::id();
        $benimMaxlar = $benimId
            ? Teklif::where('kullanici_id', $benimId)
                ->selectRaw('ilan_id, MAX(miktar) as maks')
                ->groupBy('ilan_id')
                ->pluck('maks', 'ilan_id')
            : collect();

        // Aktif müzayede varsa yalnızca onun lotları; yoksa müzayedesiz (eski) lotlar.
        $aktif = \App\Models\Muzayede::aktif();

        return Ilan::withCount('teklifler')->with('muzayede')
            ->when($aktif, fn ($q) => $q->where('muzayede_id', $aktif->id))
            ->orderBy('id')->get()
            ->map(function (Ilan $i) use ($benimId, $benimMaxlar) {
                $m = $benimMaxlar->get($i->id);

                return Sunum::ilan($i, null, $benimId, $m !== null, $m !== null ? (int) $m : null);
            })
            // Grup önceliği; grup içinde: lot no'su olanlar (açık artırma) lot no'ya göre 1,2,3…
            ->sortBy(fn (array $o) => sprintf('%d-%08d', $oncelik[$o['durum']] ?? 9, $o['lotNo'] ?? $o['id']))
            ->values();
    }
}
