<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ilan;
use App\Models\PeyAdimi;
use App\Models\Teklif;
use App\Models\User;
use App\Support\Sunum;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    /** Toplu ürün girişi formu + son içe aktarımlar. */
    public function toplu(): View
    {
        $partiler = Ilan::whereNotNull('ithal_kodu')
            ->selectRaw('ithal_kodu, count(*) as adet, max(created_at) as tarih')
            ->groupBy('ithal_kodu')
            ->orderByDesc('tarih')
            ->get();

        return view('yonetim.toplu', ['partiler' => $partiler]);
    }

    /** Excel yükle -> önizleme (henüz kaydetmez). */
    public function topluOnizle(Request $request): View|RedirectResponse
    {
        $request->validate(['excel' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240']]);

        $yol = $request->file('excel')->store('toplu');
        $sonuc = $this->exceldenOku(Storage::path($yol));

        if (empty($sonuc['items'])) {
            Storage::delete($yol);
            return back()->with('basari', "Excel'de geçerli satır bulunamadı.");
        }

        return view('yonetim.toplu_onizleme', [
            'items' => $sonuc['items'],
            'atlanan' => $sonuc['atlanan'],
            'dosya' => basename($yol),
        ]);
    }

    /** Önizleme onayı -> gerçekten ekle (parti kodu ile). */
    public function topluOnayla(Request $request): RedirectResponse
    {
        $request->validate(['dosya' => ['required', 'string']]);
        $yol = 'toplu/' . basename($request->input('dosya')); // path traversal koruması

        if (!Storage::exists($yol)) {
            return redirect()->route('yonetim.toplu')->with('basari', 'Yükleme bulunamadı, tekrar deneyin.');
        }

        $sonuc = $this->exceldenOku(Storage::path($yol));
        Storage::delete($yol);

        $kod = 'IMP-' . CarbonImmutable::now()->format('ymd-His');
        foreach ($sonuc['items'] as $it) {
            $this->itemKaydet($it, $kod);
        }

        return redirect()->route('yonetim.eserler')
            ->with('basari', count($sonuc['items']) . " eser eklendi. Parti: {$kod} (Toplu Ürün Girişi'nden geri alınabilir).");
    }

    /** Elle yapıştırma — önizlemesiz, ama parti koduyla geri alınabilir. */
    public function topluKaydet(Request $request): RedirectResponse
    {
        $request->validate(['satirlar' => ['required', 'string']]);

        $kod = 'IMP-' . CarbonImmutable::now()->format('ymd-His');
        $eklenen = 0;
        $atlanan = 0;
        foreach (preg_split('/\r\n|\r|\n/', trim((string) $request->input('satirlar'))) as $satir) {
            $satir = trim($satir);
            if ($satir === '') {
                continue;
            }
            $p = array_map('trim', explode('|', $satir));
            $fiyat = (int) preg_replace('/\D/', '', (string) ($p[2] ?? ''));
            if (($p[0] ?? '') === '' || $fiyat < 1) {
                $atlanan++;
                continue;
            }
            $this->itemKaydet([
                'baslik' => $p[0], 'alt' => $p[1] ?? '', 'aciklama' => $p[3] ?? '',
                'fiyat' => $fiyat, 'gorsel' => null,
            ], $kod);
            $eklenen++;
        }

        $mesaj = "{$eklenen} eser eklendi.";
        if ($atlanan > 0) {
            $mesaj .= " {$atlanan} satır atlandı.";
        }

        return redirect()->route('yonetim.eserler')->with('basari', $mesaj);
    }

    /** Bir içe aktarım partisini geri al (o partideki eserleri sil). */
    public function topluGeriAl(Request $request): RedirectResponse
    {
        $kod = (string) $request->input('kod');
        $adet = Ilan::where('ithal_kodu', $kod)->count();
        Ilan::where('ithal_kodu', $kod)->delete(); // teklifler cascade ile silinir

        return back()->with('basari', "{$adet} eser geri alındı (silindi).");
    }

    /**
     * Excel'i okur, geçerli satırları düz diziye çevirir (KAYDETMEZ — önizleme için).
     *
     * @return array{items: list<array>, atlanan: int}
     */
    private function exceldenOku(string $yol): array
    {
        $satirlar = IOFactory::load($yol)->getSheet(0)->toArray(null, true, true, false);
        if (count($satirlar) < 2) {
            return ['items' => [], 'atlanan' => 0];
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

        $items = [];
        $atlanan = 0;
        foreach ($satirlar as $r) {
            $eser = $al($r, $iEser);
            $sanatci = $al($r, $iSanatci);
            $fiyatRaw = $iFiyat !== null ? ($r[$iFiyat] ?? null) : null;
            $fiyat = is_numeric($fiyatRaw) ? (int) round((float) $fiyatRaw) : (int) preg_replace('/\D/', '', (string) $fiyatRaw);

            $baslik = $sanatci !== '' ? $sanatci : $eser;
            if ($baslik === '' || $fiyat < 1) {
                $atlanan++;
                continue;
            }

            $gorsel = null;
            $lot = null;
            $lotRaw = $iLot !== null ? ($r[$iLot] ?? null) : null;
            if (is_numeric($lotRaw)) {
                $lot = (int) $lotRaw;
                if (!is_file(public_path("urunler/lot-{$lot}.jpg"))) {
                    $gorsel = null;
                } else {
                    $gorsel = "/urunler/lot-{$lot}.jpg";
                }
            }

            $prov = $al($r, $iProv);
            $aciklama = trim($al($r, $iAciklama) . ($prov !== '' ? "\n\n" . $prov : ''));

            $items[] = [
                'baslik' => $baslik,
                'alt' => $eser,
                'aciklama' => $aciklama,
                'fiyat' => $fiyat,
                'lot' => $lot,
                'gorsel' => $gorsel,
            ];
        }

        return ['items' => $items, 'atlanan' => $atlanan];
    }

    /** Bir önizleme öğesini kaydeder (düşüş %5, rezerv %50 — sonra Düzenle'den ayarlanır). */
    private function itemKaydet(array $it, string $kod): void
    {
        $fiyat = (int) $it['fiyat'];
        Ilan::create([
            'baslik' => $it['baslik'],
            'alt_baslik' => ($it['alt'] ?? '') !== '' ? $it['alt'] : null,
            'aciklama' => ($it['aciklama'] ?? '') !== '' ? $it['aciklama'] : null,
            'gorsel_url' => $it['gorsel'] ?? null,
            'baslangic_fiyati' => $fiyat,
            'saatlik_dusus' => max(1, (int) round($fiyat * 0.05)),
            'rezerv_fiyat' => (int) round($fiyat * 0.5),
            'baslangic_zamani' => CarbonImmutable::now(),
            'ithal_kodu' => $kod,
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

    public function uyeGuncelle(Request $request, User $user): RedirectResponse
    {
        $veri = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
            'telefon' => ['nullable', 'string', 'max:20'],
            'sifre' => ['nullable', 'string', 'min:6'],
        ]);

        $user->name = $veri['name'];
        $user->email = $veri['email'];
        $user->telefon = $veri['telefon'] ?? null;
        if (! empty($veri['sifre'])) {
            $user->password = $veri['sifre']; // 'hashed' cast ile hash'lenir
        }
        $user->save();

        return back()->with('basari', 'Üye bilgileri güncellendi.' . (! empty($veri['sifre']) ? ' Şifre değiştirildi.' : ''));
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
            'gorsel_url' => ['nullable', 'string', 'max:1000'],
            'gorsel_dosya' => ['nullable', 'image', 'max:10240'],
            'aciklama' => ['nullable', 'string', 'max:5000'],
            'baslangic_fiyati' => ['required', 'integer', 'min:1'],
            'saatlik_dusus' => ['required', 'integer', 'min:1'],
            'dusus_periyodu' => ['required', 'integer', 'in:30,60,300,900,1800,3600'],
            'rezerv_fiyat' => ['required', 'integer', 'min:0', 'lte:baslangic_fiyati'],
        ]);

        unset($veri['gorsel_dosya']);
        if ($request->hasFile('gorsel_dosya')) {
            $veri['gorsel_url'] = $this->gorselYukle($request->file('gorsel_dosya'), 'eser');
        }

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
            'gorsel_url' => ['nullable', 'string', 'max:1000'],
            'gorsel_dosya' => ['nullable', 'image', 'max:10240'],
            'aciklama' => ['nullable', 'string', 'max:5000'],
            'baslangic_fiyati' => ['required', 'integer', 'min:1'],
            'saatlik_dusus' => ['required', 'integer', 'min:1'],
            'dusus_periyodu' => ['required', 'integer', 'in:30,60,300,900,1800,3600'],
            'rezerv_fiyat' => ['required', 'integer', 'min:0', 'lte:baslangic_fiyati'],
        ]);

        unset($veri['gorsel_dosya']);
        if ($request->hasFile('gorsel_dosya')) {
            $veri['gorsel_url'] = $this->gorselYukle($request->file('gorsel_dosya'), 'eser-' . $ilan->id);
        }

        // Henüz teklif almamış (düşüş fazındaki) eser düzenlenince fiyat düşüşü baştan başlar
        // (yeni periyot/fiyatla; aksi halde eski başlangıç zamanı yüzünden hemen tabana iner).
        if ($ilan->ilk_teklif_zamani === null) {
            $veri['baslangic_zamani'] = CarbonImmutable::now();
        }

        $ilan->update($veri);

        return redirect()->route('yonetim.eserler')->with('basari', 'Eser güncellendi: ' . $ilan->baslik);
    }

    public function ilanSil(Ilan $ilan): RedirectResponse
    {
        $baslik = $ilan->baslik;
        $ilan->delete(); // teklifler cascade ile silinir

        return back()->with('basari', 'İlan silindi: ' . $baslik);
    }

    /** Yüklenen görseli web boyutuna küçültüp public/urunler'e kaydeder, /urunler/... yolu döner. */
    private function gorselYukle(\Illuminate\Http\UploadedFile $dosya, string $adBazi): string
    {
        $dizin = public_path('urunler');
        if (!is_dir($dizin)) {
            mkdir($dizin, 0777, true);
        }

        $img = imagecreatefromstring((string) file_get_contents($dosya->getRealPath()));
        $w = imagesx($img);
        $h = imagesy($img);
        $maxG = 900;
        if ($w > $maxG) {
            $kucuk = imagescale($img, $maxG, (int) round($h * $maxG / $w));
            imagedestroy($img);
            $img = $kucuk;
        }

        $ad = $adBazi . '-' . substr(md5(uniqid('', true)), 0, 6) . '.jpg';
        imagejpeg($img, $dizin . '/' . $ad, 82);
        imagedestroy($img);

        return '/urunler/' . $ad;
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

    /** Pey (artırım) adım tablosu. */
    public function peyAdimlari(): View
    {
        return view('yonetim.pey_adimlari', ['adimlar' => PeyAdimi::orderBy('alt_sinir')->get()]);
    }

    public function peyAdimiEkle(Request $request): RedirectResponse
    {
        $veri = $request->validate([
            'alt_sinir' => ['required', 'integer', 'min:0', 'unique:pey_adimlari,alt_sinir'],
            'ust_sinir' => ['nullable', 'integer', 'gte:alt_sinir'],
            'adim' => ['required', 'integer', 'min:1'],
        ], [
            'alt_sinir.unique' => 'Bu başlangıç fiyatı için zaten bir kademe var.',
        ]);

        PeyAdimi::create($veri);

        return back()->with('basari', 'Pey adımı eklendi.');
    }

    public function peyAdimiGuncelle(Request $request, PeyAdimi $peyAdimi): RedirectResponse
    {
        $veri = $request->validate([
            'alt_sinir' => ['required', 'integer', 'min:0', \Illuminate\Validation\Rule::unique('pey_adimlari', 'alt_sinir')->ignore($peyAdimi->id)],
            'ust_sinir' => ['nullable', 'integer', 'gte:alt_sinir'],
            'adim' => ['required', 'integer', 'min:1'],
        ], [
            'alt_sinir.unique' => 'Bu başlangıç fiyatı için zaten bir kademe var.',
        ]);

        $peyAdimi->update($veri);

        return back()->with('basari', 'Pey adımı güncellendi.');
    }

    public function peyAdimiSil(PeyAdimi $peyAdimi): RedirectResponse
    {
        $peyAdimi->delete();

        return back()->with('basari', 'Pey adımı silindi.');
    }
}
