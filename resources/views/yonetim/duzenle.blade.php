@extends('layouts.yonetim')

@section('baslik', 'Eser Düzenle')

@section('content')
    <main class="yonetim">
        <h1>Eser Düzenle</h1>

        <section class="kart">
            <h2>{{ $ilan->baslik }}</h2>
            <form method="post" action="{{ route('yonetim.ilan.guncelle', $ilan) }}" enctype="multipart/form-data" class="izgara-form">
                @csrf
                <label class="genis">Başlık
                    <input type="text" name="baslik" value="{{ old('baslik', $ilan->baslik) }}" required>
                </label>
                <label class="genis">Alt Başlık
                    <input type="text" name="alt_baslik" value="{{ old('alt_baslik', $ilan->alt_baslik) }}">
                </label>
                <div class="genis gorsel-alan">
                    <span class="etiket">Görsel</span>
                    @if ($ilan->gorsel_url)
                        <img src="{{ $ilan->gorsel_url }}" alt="" class="gorsel-onizleme">
                    @else
                        <span class="alt-not">Görsel yok</span>
                    @endif
                    <label>Görsel yolu / URL
                        <input type="text" name="gorsel_url" value="{{ old('gorsel_url', $ilan->gorsel_url) }}" placeholder="/urunler/lot-1.jpg veya https://...">
                    </label>
                    <label>Bilgisayardan görsel yükle
                        <input type="file" name="gorsel_dosya" accept="image/*">
                    </label>
                </div>
                <label class="genis">Açıklama
                    <textarea name="aciklama" rows="3">{{ old('aciklama', $ilan->aciklama) }}</textarea>
                </label>
                <label>Başlangıç Fiyatı (₺)
                    <input type="number" name="baslangic_fiyati" min="1" value="{{ old('baslangic_fiyati', $ilan->baslangic_fiyati) }}" required>
                </label>
                <label>Rezerv (Taban) Fiyat (₺)
                    <input type="number" name="rezerv_fiyat" min="0" value="{{ old('rezerv_fiyat', $ilan->rezerv_fiyat) }}" required>
                    <small style="color:var(--soluk)">Teklifsiz kalırsa son 12 saatte fiyat bu tabana kadar düşer.</small>
                </label>
                <button type="submit" class="btn btn-dolu">Kaydet</button>
            </form>
            <p class="alt-not"><a href="{{ route('yonetim.eserler') }}">‹ Eserlere dön</a></p>
        </section>
    </main>
@endsection
