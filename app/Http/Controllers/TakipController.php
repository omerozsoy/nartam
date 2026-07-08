<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ilan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TakipController extends Controller
{
    /** Bir lotu takibe al / takipten çık. */
    public function toggle(Request $request, Ilan $ilan): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $vardi = $user->takipler()->where('ilan_id', $ilan->id)->exists();

        if ($vardi) {
            $user->takipler()->detach($ilan->id);
            $takip = false;
        } else {
            $user->takipler()->attach($ilan->id);
            $takip = true;
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'takip' => $takip]);
        }

        return back()->with('basari', $takip ? 'Lot takibe alındı.' : 'Takipten çıkıldı.');
    }
}
