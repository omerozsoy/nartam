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
use PhpOffice\PhpSpreadsheet\IOFactory;

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

    /** Eserler — liste. */
    public function eserler(): View
    {
        $ilanlar = Ilan::withCount('teklifler')->orderBy('id')->get()
            ->map(fn (Ilan $i) => Sunum::ilan($i) + ['teklifSayisi' => $i->teklifler_count]);

        return view('yonetim.eserler', ['ilanlar' => $ilanlar]);
    }

    /** Yeni eser ekleme formu. */
    public function eserYeni(): View
    {
        return view('yonetim.eser_yeni');
    }

    /** Toplu ürün girişi formu. */
    public function toplu(): View
    {
        return view('yonetim.toplu');
    }

    /** Toplu ürün girişi — Excel yükleme veya satır satır. */
    public function topluKaydet(Request $request): RedirectResponse
    {
        $request->validate([
            'excel' => ['nullable', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'satirlar' => ['nullable', 'string'],
            'dusus_yuzde' => ['required', 'integer', 'min:1', 'max:100'],
            'rezerv_yuzde' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $dususY = (int) $request->input('dusus_yuzde');
        $rezervY = (int) $request->input('rezerv_yuzde');

        if ($request->hasFile('excel')) {
            [$eklenen, $hatali] = $this->exceldenAktar($request->file('excel')->getRealPath(), $dususY, $rezervY);
        } elseif (trim((string) $request->input('satirlar')) !== '') {
            [$eklenen, $hatali] = $this->satirdanAktar((string) $request->input('satirlar'), $dususY, $rezervY);
        } else {
            return back()->with('basari', 'Bir Excel dosyası yükleyin ya da satırları girin.');
        }

        $mesaj = "{$eklenen} eser eklendi.";
        if ($hatali > 0) {
            $mesaj .= " {$hatali} satır atlandı (başlık/fiyat eksik).";
        }

        return redirect()->route('yonetim.eserler')->with('basari', $mesaj);
    }

    /**
     * Excel'den aktar. Sütunlar başlık adına göre bulunur:
     * Sanatçı Adı -> başlık, Eserin Adı -> alt başlık, Açıklama(+Provenance) -> açıklama, fiyat -> başlangıç.
     *
     * @return array{0: int, 1: int} [eklenen, atlanan]
     */
    private function exceldenAktar(string $yol, int $dususY, int $rezervY): array
    {
        $satirlar = IOFactory::load($yol)->getSheet(0)->toArray(null, true, true, false);
        if (count($satirlar) < 2) {
            return [0, 0];
        }

        $basliklar = array_map(fn ($h) => mb_strtolower(trim((string) $h)), array_shift($satirlar));
        $bul = static function (array $anahtarlar) use ($basliklar): ?int {
            foreach ($basliklar as $i => $b) {
                foreach ($anahtarlar as $a) {
                    if ($b !== '' && str_contains($b, $a)) {
                        return $i;
                    }
                }
            }
            return null;
        };

        $iEser = $bul(['eser']);
        $iSanatci = $bul(['sanatç']);
        $iAciklama = $bul(['açıkla', 'aciklama']);
        $iFiyat = $bul(['fiyat']);
        $iProv = $bul(['provenance', 'literature', 'not']);
        $iLot = $bul(['lot']);

        $al = static fn (array $r, ?int $i): string => $i !== null ? trim((string) ($r[$i] ?? '')) : '';

        $eklenen = 0;
        $hatali = 0;
        foreach ($satirlar as $r) {
            $eser = $al($r, $iEser);
            $sanatci = $al($r, $iSanatci);
            $fiyatRaw = $iFiyat !== null ? ($r[$iFiyat] ?? null) : null;
            $fiyat = is_numeric($fiyatRaw) ? (int) round((float) $fiyatRaw) : (int) preg_replace('/\D/', '', (string) $fiyatRaw);

            $baslik = $sanatci !== '' ? $sanatci : $eser;
            if ($baslik === '' || $fiyat < 1) {
                $hatali++;
                continue;
            }

            // Lot no'ya göre görsel: public/urunler/lot-{N}.jpg
            $gorsel = null;
            $lotRaw = $iLot !== null ? ($r[$iLot] ?? null) : null;
            if (is_numeric($lotRaw)) {
                $lot = (int) $lotRaw;
                if (is_file(public_path("urunler/lot-{$lot}.jpg"))) {
                    $gorsel = "/urunler/lot-{$lot}.jpg";
                }
            }

            $this->eserOlustur($baslik, $eser, $al($r, $iAciklama), $al($r, $iProv), $fiyat, $dususY, $rezervY, $gorsel);
            $eklenen++;
        }

        return [$eklenen, $hatali];
    }

    /** @return array{0: int, 1: int} [eklenen, atlanan] */
    private function satirdanAktar(string $metin, int $dususY, int $rezervY): array
    {
        $eklenen = 0;
        $hatali = 0;
        foreach (preg_split('/\r\n|\r|\n/', trim($metin)) as $satir) {
            $satir = trim($satir);
            if ($satir === '') {
                continue;
            }
            // Başlık | Alt başlık | Fiyat | Açıklama(ops.)
            $p = array_map('trim', explode('|', $satir));
            $baslik = $p[0] ?? '';
            $fiyat = (int) preg_replace('/\D/', '', (string) ($p[2] ?? ''));
            if ($baslik === '' || $fiyat < 1) {
                $hatali++;
                continue;
            }
            $this->eserOlustur($baslik, $p[1] ?? '', $p[3] ?? '', '', $fiyat, $dususY, $rezervY);
            $eklenen++;
        }

        return [$eklenen, $hatali];
    }

    private function eserOlustur(string $baslik, string $altBaslik, string $aciklama, string $prov, int $fiyat, int $dususY, int $rezervY, ?string $gorselUrl = null): void
    {
        $tamAciklama = trim($aciklama . ($prov !== '' ? "\n\n" . $prov : ''));
        Ilan::create([
            'baslik' => $baslik,
            'alt_baslik' => $altBaslik !== '' ? $altBaslik : null,
            'aciklama' => $tamAciklama !== '' ? $tamAciklama : null,
            'gorsel_url' => $gorselUrl,
            'baslangic_fiyati' => $fiyat,
            'saatlik_dusus' => max(1, (int) round($fiyat * $dususY / 100)),
            'rezerv_fiyat' => (int) round($fiyat * $rezervY / 100),
            'baslangic_zamani' => CarbonImmutable::now(),
        ]);
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

        return redirect()->route('yonetim.eserler')->with('basari', 'Eser oluşturuldu: ' . $veri['baslik']);
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
