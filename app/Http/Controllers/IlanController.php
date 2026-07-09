<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ilan;
use App\Models\Muzayede;
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
        $benimId = Auth::id();

        // Üst SLIDER (hero): yönetimden seçilen lotlar.
        $heroLotlar = Ilan::where('carusel', true)->with('muzayede')->withCount('teklifler')
            ->orderByRaw('carusel_sira is null')->orderBy('carusel_sira')
            ->orderByRaw('lot_no is null')->orderBy('lot_no')->limit(8)->get()
            ->map(fn (Ilan $i) => Sunum::ilan($i, null, $benimId));

        // Alt CARUSEL (coverflow): yönetimden seçilen lotlar; yoksa Açık Artırma otomatik.
        $coverflow = Ilan::where('coverflow', true)->with('muzayede')->withCount('teklifler')
            ->orderByRaw('coverflow_sira is null')->orderBy('coverflow_sira')
            ->orderByRaw('lot_no is null')->orderBy('lot_no')->limit(12)->get()
            ->map(fn (Ilan $i) => Sunum::ilan($i, null, $benimId));
        if ($coverflow->isEmpty()) {
            $coverflow = $this->siraliOzetler()->where('durum', 'acik_artirma')->take(12)->values();
        }

        // Alt ÜRÜN KARTLARI: yönetimden seçilen lotlar (satır satır, 4'erli).
        $kartlar = Ilan::where('kart', true)->with('muzayede')->withCount('teklifler')
            ->orderByRaw('kart_sira is null')->orderBy('kart_sira')
            ->orderByRaw('lot_no is null')->orderBy('lot_no')->limit(24)->get()
            ->map(fn (Ilan $i) => Sunum::ilan($i, null, $benimId));

        $satisGorsel = $heroLotlar->first()['gorselUrl'] ?? ($coverflow->first()['gorselUrl'] ?? null);

        return view('ilanlar.anasayfa', [
            'hero' => $heroLotlar,
            'vitrin' => $coverflow,
            'kartlar' => $kartlar,
            'satisGorsel' => $satisGorsel,
            'muzayede' => Muzayede::aktif(),
        ]);
    }

    /** Müzayedeler listesi (herkese açık). */
    public function muzayedeler(): View
    {
        return view('muzayede.liste', [
            'muzayedeler' => Muzayede::withCount('ilanlar')->orderByDesc('baslangic')->get(),
        ]);
    }

    /** Bir müzayedenin lotları (herkese açık). */
    public function muzayedeGoster(Muzayede $muzayede): View
    {
        return view('ilanlar.liste', [
            'gruplar' => $this->siraliOzetler($muzayede)->groupBy('durum'),
            'muzayede' => $muzayede,
            'vitrinGoster' => false,
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
        $takip = $benimId && \Illuminate\Support\Facades\DB::table('takipler')
            ->where('user_id', $benimId)->where('ilan_id', $ilan->id)->exists();

        return view('ilanlar.detay', [
            'ilan' => $ilan,
            'ozet' => Sunum::ilan($ilan, null, $benimId, $benimMax !== null, $benimMax !== null ? (int) $benimMax : null, $takip),
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
    private function siraliOzetler(?Muzayede $muzayede = null): Collection
    {
        $oncelik = ['acik_artirma' => 0, 'dusuyor' => 1, 'yakinda' => 2, 'kapandi' => 3];
        $benimId = Auth::id();
        $benimMaxlar = $benimId
            ? Teklif::where('kullanici_id', $benimId)
                ->selectRaw('ilan_id, MAX(miktar) as maks')
                ->groupBy('ilan_id')
                ->pluck('maks', 'ilan_id')
            : collect();
        $takipSet = $benimId
            ? array_flip(\Illuminate\Support\Facades\DB::table('takipler')->where('user_id', $benimId)->pluck('ilan_id')->all())
            : [];

        // Belirli müzayede verildiyse onun lotları; yoksa aktif müzayedenin lotları.
        $hedef = $muzayede ?? Muzayede::aktif();

        return Ilan::withCount('teklifler')->with('muzayede')
            ->when($hedef, fn ($q) => $q->where('muzayede_id', $hedef->id))
            ->orderBy('id')->get()
            ->map(function (Ilan $i) use ($benimId, $benimMaxlar, $takipSet) {
                $m = $benimMaxlar->get($i->id);

                return Sunum::ilan($i, null, $benimId, $m !== null, $m !== null ? (int) $m : null, isset($takipSet[$i->id]));
            })
            // Grup önceliği; grup içinde: lot no'su olanlar (açık artırma) lot no'ya göre 1,2,3…
            ->sortBy(fn (array $o) => sprintf('%d-%08d', $oncelik[$o['durum']] ?? 9, $o['lotNo'] ?? $o['id']))
            ->values();
    }
}
