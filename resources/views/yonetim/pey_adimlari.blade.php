@extends('layouts.yonetim')

@section('baslik', 'Pey Adımları')

@section('content')
    <main class="yonetim">
        <h1>Pey Adımları</h1>
        <p class="alt-not" style="margin-bottom:1.2rem">
            Açık artırmada fiyat aralığına göre artırım (pey) tutarı. Örn. <strong>0 – 1.000 ₺ arası 20 ₺</strong>.
            Bitiş boş bırakılırsa "ve üzeri" olur.
        </p>

        <section class="kart">
            <h2>Yeni Kademe</h2>
            <form method="post" action="{{ route('yonetim.pey.ekle') }}" class="izgara-form">
                @csrf
                <label>Başlangıç Fiyatı (₺)
                    <input type="number" name="alt_sinir" min="0" value="{{ old('alt_sinir') }}" required>
                </label>
                <label>Bitiş Fiyatı (₺) <small>(boş = ve üzeri)</small>
                    <input type="number" name="ust_sinir" min="0" value="{{ old('ust_sinir') }}">
                </label>
                <label>Pey Adımı (₺)
                    <input type="number" name="adim" min="1" value="{{ old('adim') }}" required>
                </label>
                <button type="submit" class="btn btn-dolu">Ekle</button>
            </form>
        </section>

        <section class="kart">
            <h2>Mevcut Kademeler</h2>
            @if ($adimlar->isEmpty())
                <p class="alt-not">Henüz kademe yok — varsayılan kullanılır (0→50, 1.000→100, 5.000→250).</p>
            @else
                @foreach ($adimlar as $a)
                    <div class="pey-satir">
                        <form method="post" action="{{ route('yonetim.pey.guncelle', $a) }}" class="pey-duzen">
                            @csrf
                            <label>Başlangıç (₺)
                                <input type="number" name="alt_sinir" value="{{ $a->alt_sinir }}" min="0" required>
                            </label>
                            <label>Bitiş (₺)
                                <input type="number" name="ust_sinir" value="{{ $a->ust_sinir }}" min="0" placeholder="ve üzeri">
                            </label>
                            <label>Adım (₺)
                                <input type="number" name="adim" value="{{ $a->adim }}" min="1" required>
                            </label>
                            <button type="submit" class="btn">Güncelle</button>
                        </form>
                        <form method="post" action="{{ route('yonetim.pey.sil', $a) }}" onsubmit="return confirm('Bu kademe silinsin mi?')">
                            @csrf
                            <button type="submit" class="baglanti-buton" style="color:var(--kritik)">Sil</button>
                        </form>
                    </div>
                @endforeach
            @endif
        </section>
    </main>
@endsection
