@extends('layouts.app')

@section('baslik', 'Adreslerim')

@section('content')
    <div class="kap hesap">
        <h1 class="hesap-baslik">Adreslerim</h1>
        @include('hesap._nav')

        <div class="adres-izgara">
            <section class="kart">
                <h2>Yeni Adres</h2>
                <form method="post" action="{{ route('adresler.ekle') }}" class="izgara-form">
                    @csrf
                    <label>Adres Türü
                        <select name="tur" required>
                            <option value="teslimat">Teslimat Adresi</option>
                            <option value="fatura">Fatura Adresi</option>
                        </select>
                    </label>
                    <label>Ad Soyad
                        <input type="text" name="ad_soyad" value="{{ old('ad_soyad', auth()->user()->name) }}" required>
                    </label>
                    <label>Telefon
                        <input type="tel" name="telefon" value="{{ old('telefon', auth()->user()->telefon) }}">
                    </label>
                    <label>İl
                        <input type="text" name="il" value="{{ old('il') }}">
                    </label>
                    <label>İlçe
                        <input type="text" name="ilce" value="{{ old('ilce') }}">
                    </label>
                    <label>Posta Kodu
                        <input type="text" name="posta_kodu" value="{{ old('posta_kodu') }}">
                    </label>
                    <label class="genis">Açık Adres
                        <textarea name="adres" rows="3" required>{{ old('adres') }}</textarea>
                    </label>
                    <button type="submit" class="btn btn-dolu">Adres Ekle</button>
                </form>
            </section>

            <section>
                @forelse ($adresler as $adres)
                    <div class="adres-kart">
                        <div class="adres-ust">
                            <span class="rozet rozet-{{ $adres->tur === 'fatura' ? 'acik_artirma' : 'dusuyor' }}">{{ $adres->turEtiket() }}</span>
                            <form method="post" action="{{ route('adresler.sil', $adres) }}"
                                  onsubmit="return confirm('Adresi silmek istiyor musunuz?')">
                                @csrf
                                <button type="submit" class="baglanti-buton" style="color:var(--kritik)">Sil</button>
                            </form>
                        </div>
                        <div class="adres-ad">{{ $adres->ad_soyad }}</div>
                        @if ($adres->telefon)<div class="lot-alt">{{ $adres->telefon }}</div>@endif
                        <p class="adres-metin">{{ $adres->adres }}</p>
                        <div class="lot-alt">{{ trim(($adres->ilce ? $adres->ilce . ' / ' : '') . $adres->il . ' ' . $adres->posta_kodu) }}</div>
                    </div>
                @empty
                    <p class="hesap-bos">Henüz adres eklemediniz.</p>
                @endforelse
            </section>
        </div>
    </div>
@endsection
