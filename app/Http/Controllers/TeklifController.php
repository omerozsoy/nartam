<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ilan;
use App\Services\TeklifServisi;
use App\Support\Sunum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeklifController extends Controller
{
    public function store(Request $request, TeklifServisi $servis): JsonResponse|RedirectResponse
    {
        $veri = $request->validate([
            'ilan_id' => ['required', 'integer', 'exists:ilanlar,id'],
            'miktar' => ['required', 'integer', 'min:1'],
        ]);

        if ($request->user()->engelli) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'miktar' => 'Hesabınız engellendiği için teklif veremezsiniz.',
            ]);
        }

        $ilan = Ilan::findOrFail($veri['ilan_id']);

        // Geçersizse ValidationException fırlatır; Laravel AJAX'ta 422 JSON,
        // formda geri yönlendirme + hata olarak döndürür.
        $ilan = $servis->teklifVer($ilan, $request->user(), (int) $veri['miktar']);
        $ozet = Sunum::ilan($ilan);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'ilan' => $ozet]);
        }

        return back()->with('basari', 'Teklifiniz alındı: ' . $ozet['guncelFiyatBicim']);
    }
}
