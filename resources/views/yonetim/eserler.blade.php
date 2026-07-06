@extends('layouts.yonetim')

@section('baslik', 'Eserler')

@section('content')
    <main class="yonetim">
        <h1>Eserler</h1>

        <section class="kart">
            <h2>Yeni Eser</h2>
            <form method="post" action="{{ route('yonetim.ilan') }}" class="izgara-form">
                @csrf
                <label class="genis">Başlık
                    <input type="text" name="baslik" value="{{ old('baslik') }}" required>
                </label>
                <label class="genis">Alt Başlık (eser/kısa açıklama)
                    <input type="text" name="alt_baslik" value="{{ old('alt_baslik') }}">
                </label>
                <label class="genis">Görsel URL
                    <input type="url" name="gorsel_url" value="{{ old('gorsel_url') }}" placeholder="https://...">
                </label>
                <label class="genis">Açıklama
                    <textarea name="aciklama" rows="3">{{ old('aciklama') }}</textarea>
                </label>
                <label>Başlangıç Fiyatı (₺)
                    <input type="number" name="baslangic_fiyati" min="1" value="{{ old('baslangic_fiyati', 1000) }}" required>
                </label>
                <label>Saatlik Düşüş (₺)
                    <input type="number" name="saatlik_dusus" min="1" value="{{ old('saatlik_dusus', 100) }}" required>
                </label>
                <label>Rezerv (Taban) Fiyat (₺)
                    <input type="number" name="rezerv_fiyat" min="0" value="{{ old('rezerv_fiyat', 500) }}" required>
                </label>
                <button type="submit" class="btn btn-dolu">Eser Oluştur</button>
            </form>
            <p class="alt-not">Eser hemen "düşen fiyat" fazında başlar; ilk teklifle açık artırmaya döner.</p>
        </section>

        <section class="kart">
            <h2>Mevcut Eserler</h2>
            <table class="tablo">
                <thead>
                <tr><th>#</th><th>Lot</th><th>Başlık</th><th>Durum</th><th>Güncel Fiyat</th><th>Teklif</th><th></th></tr>
                </thead>
                <tbody>
                @foreach ($ilanlar as $ilan)
                    <tr>
                        <td>{{ $ilan['id'] }}</td>
                        <td>{{ $ilan['lotNo'] ?? '—' }}</td>
                        <td>{{ $ilan['baslik'] }}</td>
                        <td><span class="rozet rozet-{{ $ilan['durum'] }}">{{ $ilan['durumEtiket'] }}</span></td>
                        <td>{{ $ilan['guncelFiyatBicim'] }}</td>
                        <td>
                            @if ($ilan['teklifSayisi'] > 0)
                                <a href="{{ route('yonetim.teklifler', ['ilan' => $ilan['id']]) }}">{{ $ilan['teklifSayisi'] }} teklif</a>
                            @else
                                0
                            @endif
                        </td>
                        <td style="white-space:nowrap">
                            <a href="{{ route('yonetim.ilan.duzenle', $ilan['id']) }}">Düzenle</a>
                            <form method="post" action="{{ route('yonetim.ilan.sil', $ilan['id']) }}" style="display:inline;margin-left:.6rem"
                                  onsubmit="return confirm('Bu eseri ve tekliflerini silmek istediğinize emin misiniz?')">
                                @csrf
                                <button type="submit" class="baglanti-buton" style="color:var(--kritik)">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    </main>
@endsection
