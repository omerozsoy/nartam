@extends('layouts.yonetim')

@section('baslik', 'Yeni Eser')

@section('content')
    <main class="yonetim">
        <h1>Yeni Eser Ekle</h1>

        <section class="kart">
            <form method="post" action="{{ route('yonetim.ilan') }}" class="izgara-form">
                @csrf
                <label class="genis">Başlık
                    <input type="text" name="baslik" value="{{ old('baslik') }}" required autofocus>
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
                <label>Düşüş Miktarı (₺)
                    <input type="number" name="saatlik_dusus" min="1" value="{{ old('saatlik_dusus', 100) }}" required>
                </label>
                <label>Düşüş Periyodu
                    <select name="dusus_periyodu" required>
                        <option value="1" @selected(old('dusus_periyodu') == 1)>Saniyede bir</option>
                        <option value="60" @selected(old('dusus_periyodu') == 60)>Dakikada bir</option>
                        <option value="3600" @selected(old('dusus_periyodu', 3600) == 3600)>Saatte bir</option>
                    </select>
                </label>
                <label>Rezerv (Taban) Fiyat (₺)
                    <input type="number" name="rezerv_fiyat" min="0" value="{{ old('rezerv_fiyat', 500) }}" required>
                </label>
                <button type="submit" class="btn btn-dolu">Eser Oluştur</button>
            </form>
            <p class="alt-not">Eser hemen "düşen fiyat" fazında başlar; ilk teklifle açık artırmaya döner.</p>
        </section>
    </main>
@endsection
