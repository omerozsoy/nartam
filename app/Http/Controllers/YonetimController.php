<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ilan;
use App\Models\Teklif;
use App\Models\User;
use App\Support\Sunum;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class YonetimController extends Controller
{
    /** Panel (dashboard) — özet istatistik. */
    public function index(): View
    {
        $ilanlar = Ilan::withCount('teklifler')->get()
            ->map(fn (Ilan $i) => Sunum::ilan($i));

        $istatistik = [
            'uye' => User::where('rol', '!=', 'yonetici')->count(),
            'ilan' => $ilanlar->count(),
            'acikArtirma' => $ilanlar->where('durum', 'acik_artirma')->count(),
            'dusuyor' => $ilanlar->where('durum', 'dusuyor')->count(),
            'teklif' => Teklif::count(),
            'toplamDeger' => number_format((int) $ilanlar->sum('guncelFiyat'), 0, ',', '.') . ' ₺',
        ];

        return view('yonetim.panel', ['istatistik' => $istatistik]);
    }

    /** Eserler — liste + yeni eser formu. */
    public function eserler(): View
    {
        $ilanlar = Ilan::withCount('teklifler')->orderBy('id')->get()
            ->map(fn (Ilan $i) => Sunum::ilan($i) + ['teklifSayisi' => $i->teklifler_count]);

        return view('yonetim.eserler', ['ilanlar' => $ilanlar]);
    }

    /** Üye detayı: bilgileri, adresleri, teklifleri. */
    public function uye(User $user): View
    {
        $user->loadCount('teklifler');
        $teklifler = $user->teklifler()->with('ilan')->latest('zaman')->get();

        return view('yonetim.uye', [
            'uye' => $user,
            'teklifler' => $teklifler,
            'adresler' => $user->adresler()->latest()->get(),
        ]);
    }

    public function uyeEngelle(User $user): RedirectResponse
    {
        if ($user->yonetici()) {
            return back()->with('basari', 'Yönetici engellenemez.');
        }

        $user->update(['engelli' => !$user->engelli]);

        return back()->with('basari', $user->engelli ? 'Üye engellendi.' : 'Üye engeli kaldırıldı.');
    }

    public function ilanOlustur(Request $request): RedirectResponse
    {
        $veri = $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'alt_baslik' => ['nullable', 'string', 'max:255'],
            'gorsel_url' => ['nullable', 'url', 'max:1000'],
            'aciklama' => ['nullable', 'string', 'max:5000'],
            'baslangic_fiyati' => ['required', 'integer', 'min:1'],
            'saatlik_dusus' => ['required', 'integer', 'min:1'],
            'rezerv_fiyat' => ['required', 'integer', 'min:0', 'lte:baslangic_fiyati'],
        ]);

        Ilan::create([
            ...$veri,
            'baslangic_zamani' => CarbonImmutable::now(),
        ]);

        return back()->with('basari', 'İlan oluşturuldu: ' . $veri['baslik']);
    }

    public function ilanDuzenle(Ilan $ilan): View
    {
        return view('yonetim.duzenle', ['ilan' => $ilan]);
    }

    public function ilanGuncelle(Request $request, Ilan $ilan): RedirectResponse
    {
        $veri = $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'alt_baslik' => ['nullable', 'string', 'max:255'],
            'gorsel_url' => ['nullable', 'url', 'max:1000'],
            'aciklama' => ['nullable', 'string', 'max:5000'],
            'baslangic_fiyati' => ['required', 'integer', 'min:1'],
            'saatlik_dusus' => ['required', 'integer', 'min:1'],
            'rezerv_fiyat' => ['required', 'integer', 'min:0', 'lte:baslangic_fiyati'],
        ]);

        $ilan->update($veri);

        return redirect()->route('yonetim.eserler')->with('basari', 'Eser güncellendi: ' . $ilan->baslik);
    }

    public function ilanSil(Ilan $ilan): RedirectResponse
    {
        $baslik = $ilan->baslik;
        $ilan->delete(); // teklifler cascade ile silinir

        return back()->with('basari', 'İlan silindi: ' . $baslik);
    }

    /** Üyeler (kayıtlı kullanıcılar). */
    public function uyeler(): View
    {
        $uyeler = User::withCount('teklifler')->orderByDesc('id')->get();

        return view('yonetim.uyeler', ['uyeler' => $uyeler]);
    }

    /** Pey verenler — tüm teklifler (isteğe bağlı ilana göre filtreli). */
    public function teklifler(Request $request): View
    {
        $ilanId = $request->integer('ilan') ?: null;

        $sorgu = Teklif::with(['kullanici', 'ilan'])->latest('zaman');
        if ($ilanId) {
            $sorgu->where('ilan_id', $ilanId);
        }

        return view('yonetim.teklifler', [
            'teklifler' => $sorgu->get(),
            'ilan' => $ilanId ? Ilan::find($ilanId) : null,
        ]);
    }
}
