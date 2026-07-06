@extends('layouts.app')

@section('baslik', 'İlan Düzenle')

@section('content')
    <main class="yonetim">
        <h1>İlan Düzenle</h1>
        @include('yonetim._nav')

        <section class="kart">
            <h2>{{ $ilan->baslik }}</h2>
            <form method="post" action="{{ route('yonetim.ilan.guncelle', $ilan) }}" class="izgara-form">
                @csrf
                <label class="genis">Başlık
                    <input type="text" name="baslik" value="{{ old('baslik', $ilan->baslik) }}" required>
                </label>
                <label class="genis">Alt Başlık
                    <input type="text" name="alt_baslik" value="{{ old('alt_baslik', $ilan->alt_baslik) }}">
                </label>
                <label class="genis">Görsel URL
                    <input type="url" name="gorsel_url" value="{{ old('gorsel_url', $ilan->gorsel_url) }}" placeholder="https://...">
                </label>
                <label class="genis">Açıklama
                    <textarea name="aciklama" rows="3">{{ old('aciklama', $ilan->aciklama) }}</textarea>
                </label>
                <label>Başlangıç Fiyatı (₺)
                    <input type="number" name="baslangic_fiyati" min="1" value="{{ old('baslangic_fiyati', $ilan->baslangic_fiyati) }}" required>
                </label>
                <label>Saatlik Düşüş (₺)
                    <input type="number" name="saatlik_dusus" min="1" value="{{ old('saatlik_dusus', $ilan->saatlik_dusus) }}" required>
                </label>
                <label>Rezerv (Taban) Fiyat (₺)
                    <input type="number" name="rezerv_fiyat" min="0" value="{{ old('rezerv_fiyat', $ilan->rezerv_fiyat) }}" required>
                </label>
                <button type="submit" class="btn btn-dolu">Kaydet</button>
            </form>
            <p class="alt-not"><a href="{{ route('yonetim') }}">‹ İlanlara dön</a></p>
        </section>
    </main>
@endsection
