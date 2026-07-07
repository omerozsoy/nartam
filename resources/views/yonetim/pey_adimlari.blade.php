@extends('layouts.yonetim')

@section('baslik', 'Pey Adımları')

@section('content')
    <main class="yonetim">
        <h1>Pey Adımları</h1>
        <p class="alt-not" style="margin-bottom:1.2rem">
            Açık artırmada fiyat aralığına göre artırım (pey) tutarı. Örn. <strong>0 ₺'den itibaren 20 ₺</strong>.
        </p>

        <section class="kart">
            <h2>Yeni Kademe</h2>
            <form method="post" action="{{ route('yonetim.pey.ekle') }}" class="izgara-form">
                @csrf
                <label>Başlangıç Fiyatı (₺) <small>(bu fiyattan itibaren)</small>
                    <input type="number" name="alt_sinir" min="0" value="{{ old('alt_sinir') }}" required>
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
                <table class="tablo">
                    <thead><tr><th>Fiyat Aralığı</th><th>Pey Adımı</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($adimlar as $i => $a)
                        @php($ust = isset($adimlar[$i + 1]) ? number_format($adimlar[$i + 1]->alt_sinir - 1, 0, ',', '.') . ' ₺' : 've üzeri')
                        <tr>
                            <td>{{ number_format($a->alt_sinir, 0, ',', '.') }} ₺ – {{ $ust }}</td>
                            <td>{{ number_format($a->adim, 0, ',', '.') }} ₺</td>
                            <td>
                                <form method="post" action="{{ route('yonetim.pey.sil', $a) }}" onsubmit="return confirm('Bu kademe silinsin mi?')">
                                    @csrf
                                    <button type="submit" class="baglanti-buton" style="color:var(--kritik)">Sil</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </main>
@endsection
