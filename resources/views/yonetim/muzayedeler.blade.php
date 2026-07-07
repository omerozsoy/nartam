@extends('layouts.yonetim')

@section('baslik', 'Müzayedeler')

@section('content')
    <main class="yonetim">
        <div class="yonetim-ust">
            <h1>Müzayedeler</h1>
            <div class="yonetim-eylem">
                <a class="btn btn-dolu" href="{{ route('yonetim.muzayede.yeni') }}">Yeni Müzayede</a>
            </div>
        </div>

        <p class="alt-not" style="margin-bottom:1rem">
            Yeni müzayede oluştur → <strong>Aktif Yap</strong> → Toplu Ürün Girişi ile lotları yükle →
            <strong>Programı Uygula</strong> (lotların kapanış zamanlarını atar). Sitede yalnızca aktif müzayedenin lotları görünür.
        </p>

        <section class="kart">
            <table class="tablo">
                <thead>
                <tr><th>No</th><th>İsim</th><th>Başlangıç</th><th>İlk Kapanış</th><th>Lot</th><th>Durum</th><th></th></tr>
                </thead>
                <tbody>
                @forelse ($muzayedeler as $m)
                    <tr>
                        <td>{{ $m->no }}</td>
                        <td>{{ $m->ad }}</td>
                        <td>{{ $m->baslangic->format('d.m.Y H:i') }}</td>
                        <td>{{ $m->bitis->format('d.m.Y H:i') }}</td>
                        <td>{{ $m->ilanlar_count }}</td>
                        <td>
                            @if ($m->aktif)
                                <span class="rozet rozet-acik_artirma">Aktif</span>
                            @else
                                <form method="post" action="{{ route('yonetim.muzayede.aktif', $m) }}" style="display:inline">
                                    @csrf<button type="submit" class="baglanti-buton">Aktif Yap</button>
                                </form>
                            @endif
                        </td>
                        <td style="white-space:nowrap">
                            <form method="post" action="{{ route('yonetim.muzayede.program', $m) }}" style="display:inline"
                                  onsubmit="return confirm('{{ $m->ad }} lotlarının kapanış zamanları atanacak. Devam?')">
                                @csrf<button type="submit" class="baglanti-buton">Programı Uygula</button>
                            </form>
                            <a href="{{ route('yonetim.muzayede.duzenle', $m) }}" style="margin-left:.6rem">Düzenle</a>
                            <form method="post" action="{{ route('yonetim.muzayede.sil', $m) }}" style="display:inline;margin-left:.6rem"
                                  onsubmit="return confirm('Müzayede silinsin mi? (Lotlar silinmez, bağı kalkar)')">
                                @csrf<button type="submit" class="baglanti-buton" style="color:var(--kritik)">Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="alt-not">Henüz müzayede yok. "Yeni Müzayede" ile başlayın.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>
    </main>
@endsection
