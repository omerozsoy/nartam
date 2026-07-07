@extends('layouts.yonetim')

@section('baslik', 'Eserler')

@section('content')
    <main class="yonetim">
        <div class="yonetim-ust">
            <h1>Eserler</h1>
            <div class="yonetim-eylem">
                <a class="btn btn-dolu" href="{{ route('yonetim.eser.yeni') }}">Yeni Eser Ekle</a>
                <a class="btn" href="{{ route('yonetim.toplu') }}">Toplu Ürün Girişi</a>
                <form method="post" action="{{ route('yonetim.eserler.sil-hepsi') }}" style="display:inline"
                      onsubmit="return confirm('TÜM eserler ve teklifleri silinecek. Emin misiniz?')">
                    @csrf
                    <button type="submit" class="btn" style="color:var(--kritik);border-color:var(--kritik)">Tüm Eserleri Sil</button>
                </form>
            </div>
        </div>

        <section class="kart">
            <table class="tablo">
                <thead>
                <tr><th>Stok No</th><th>Lot</th><th>Başlık</th><th>Durum</th><th>Güncel Fiyat</th><th>Teklif</th><th></th></tr>
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
