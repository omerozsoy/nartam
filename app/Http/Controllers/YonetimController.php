<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ilan;
use App\Support\Sunum;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class YonetimController extends Controller
{
    public function index(): View
    {
        $ilanlar = Ilan::orderBy('id')->get()->map(fn (Ilan $i) => Sunum::ilan($i));

        return view('yonetim.index', ['ilanlar' => $ilanlar]);
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
}
