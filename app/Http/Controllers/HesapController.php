<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Durum;
use App\Models\Ilan;
use App\Models\User;
use App\Support\Sunum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HesapController extends Controller
{
    /** Kullanıcının pey verdiği (teklif verdiği) eserler. */
    public function index(Request $request): View
    {
        $satirlar = $this->satirlar($request->user());

        $takip = $request->user()->takipler()->with('muzayede')->withCount('teklifler')->orderByPivot('created_at', 'desc')->get()
            ->map(fn (Ilan $i) => Sunum::ilan($i, null, $request->user()->id, false, null, true));

        return view('hesap.index', [
            'kazandiklarim' => $satirlar->where('durumum', 'kazandi')->values(),
            'diger' => $satirlar->where('durumum', '!=', 'kazandi')->values(),
            'takipEttiklerim' => $takip,
        ]);
    }

    /** Canlı güncelleme için JSON. */
    public function api(Request $request): JsonResponse
    {
        return response()->json($this->satirlar($request->user()));
    }

    /** Kişisel bilgiler formu. */
    public function bilgiler(Request $request): View
    {
        return view('hesap.bilgiler', ['kullanici' => $request->user()]);
    }

    /** Kişisel bilgileri günceller (ad, e-posta, telefon, şifre). */
    public function bilgilerGuncelle(Request $request): RedirectResponse
    {
        $kullanici = $request->user();

        $veri = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($kullanici->id)],
            'telefon' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ], [], [
            'name' => 'ad soyad',
            'email' => 'e-posta',
            'telefon' => 'telefon',
            'password' => 'şifre',
        ]);

        $kullanici->name = $veri['name'];
        $kullanici->email = $veri['email'];
        $kullanici->telefon = $veri['telefon'] ?? null;
        if (! empty($veri['password'])) {
            $kullanici->password = Hash::make($veri['password']);
        }
        $kullanici->save();

        return back()->with('basari', 'Bilgileriniz güncellendi.');
    }

    /** @return Collection<int, array> */
    private function satirlar(User $kullanici): Collection
    {
        $ilanIdleri = $kullanici->teklifler()->pluck('ilan_id')->unique();

        return Ilan::whereIn('id', $ilanIdleri)
            ->withCount('teklifler')
            ->with('muzayede')
            ->get()
            ->map(function (Ilan $ilan) use ($kullanici) {
                $ozet = Sunum::ilan($ilan);
                $durum = $ilan->durum();
                $onde = $ilan->son_teklif_sahibi === $kullanici->name;

                $ozet['muzayedeId'] = $ilan->muzayede_id;
                $ozet['muzayedeBaslik'] = $ilan->muzayede
                    ? $ilan->muzayede->no . '. Müzayede · ' . $ilan->muzayede->ad
                    : 'Müzayede';

                $benim = (int) $kullanici->teklifler()->where('ilan_id', $ilan->id)->max('miktar');
                $ozet['benimTeklifim'] = $benim;
                $ozet['benimTeklifimBicim'] = number_format($benim, 0, ',', '.') . ' ₺';

                $ozet['durumum'] = match (true) {
                    $durum === Durum::KAPANDI && $onde => 'kazandi',
                    $durum === Durum::KAPANDI => 'kaybetti',
                    $onde => 'onde',
                    default => 'geride',
                };

                return $ozet;
            })
            ->sortBy(fn (array $o) => sprintf('%d-%012d', $o['durum'] === 'kapandi' ? 1 : 0, PHP_INT_MAX - $o['benimTeklifim']))
            ->values();
    }
}
