<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ilan;
use App\Support\Sunum;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class IlanController extends Controller
{
    public function index(): View
    {
        $ilanlar = Ilan::orderBy('id')->get()->map(fn (Ilan $i) => Sunum::ilan($i));

        return view('ilanlar.liste', ['ilanlar' => $ilanlar]);
    }

    /** Canlı güncelleme (polling) için JSON. */
    public function api(): JsonResponse
    {
        $ilanlar = Ilan::orderBy('id')->get()->map(fn (Ilan $i) => Sunum::ilan($i));

        return response()->json($ilanlar);
    }
}
