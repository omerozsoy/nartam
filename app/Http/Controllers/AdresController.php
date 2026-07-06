<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Adres;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdresController extends Controller
{
    public function index(Request $request): View
    {
        return view('hesap.adresler', [
            'adresler' => $request->user()->adresler()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $veri = $request->validate([
            'tur' => ['required', Rule::in(['teslimat', 'fatura'])],
            'ad_soyad' => ['required', 'string', 'max:255'],
            'telefon' => ['nullable', 'string', 'max:20'],
            'il' => ['nullable', 'string', 'max:100'],
            'ilce' => ['nullable', 'string', 'max:100'],
            'adres' => ['required', 'string', 'max:1000'],
            'posta_kodu' => ['nullable', 'string', 'max:10'],
        ]);

        $request->user()->adresler()->create($veri);

        return back()->with('basari', 'Adres eklendi.');
    }

    public function destroy(Request $request, Adres $adres): RedirectResponse
    {
        abort_unless($adres->user_id === $request->user()->id, 403);
        $adres->delete();

        return back()->with('basari', 'Adres silindi.');
    }
}
