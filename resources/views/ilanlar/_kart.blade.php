@php($detayUrl = route('ilan.goster', $ilan['id']))
<article
    class="lot durum-{{ $ilan['durum'] }}"
    data-id="{{ $ilan['id'] }}"
    data-durum="{{ $ilan['durum'] }}"
    @if ($ilan['bitisTs']) data-bitis="{{ $ilan['bitisTs'] }}" @endif
    @if ($ilan['sonrakiDususTs']) data-sonraki-dusus="{{ $ilan['sonrakiDususTs'] }}" @endif
    data-min="{{ $ilan['minTeklif'] }}"
>
    <a href="{{ $detayUrl }}" class="lot-gorsel {{ $ilan['gorselUrl'] ? '' : 'bos' }}">
        @if ($ilan['gorselUrl'])
            <img src="{{ $ilan['gorselUrl'] }}" alt="{{ $ilan['baslik'] }}">
        @else
            {{ mb_strtoupper(mb_substr($ilan['baslik'], 0, 1)) }}
            <small>Yeni Müzayede</small>
        @endif
    </a>

    <span class="rozet">{{ $ilan['durumEtiket'] }}</span>

    @if ($ilan['durum'] !== 'kapandi')
        <p class="sayac" role="timer" data-alan="sayac">--:--</p>
    @endif

    @if ($ilan['lotNo'])
        <div class="lot-no">Lot {{ $ilan['lotNo'] }}</div>
    @endif

    <h2 class="lot-baslik"><a href="{{ $detayUrl }}">{{ $ilan['baslik'] }}</a></h2>
    <div class="lot-alt">{{ $ilan['altBaslik'] }}</div>

    <div class="lot-satir fiyat-satir">
        <div class="fiyat" data-alan="fiyat" data-deger="{{ $ilan['guncelFiyat'] }}">{{ $ilan['guncelFiyatBicim'] }}</div>
    </div>

    @if ($ilan['durum'] !== 'kapandi')
        @auth
            <div class="onde-bilgi" data-alan="onde" @unless(auth()->id() === $ilan['liderId']) hidden @endunless>★ Şu an öndesiniz</div>
            <form class="teklif-form" data-alan="teklif-form">
                @csrf
                <input type="hidden" name="ilan_id" value="{{ $ilan['id'] }}">
                <input type="number" name="miktar" step="1"
                       min="{{ $ilan['minTeklif'] }}" value="{{ $ilan['minTeklif'] }}"
                       data-alan="miktar" required title="Maksimum teklifiniz (gizli)">
                <button type="submit" class="btn btn-dolu">
                    {{ $ilan['durum'] === 'dusuyor' ? 'Bu Fiyattan 24 Saatlik Müzayedeyi Başlat' : 'Teklif Ver' }}
                </button>
                <span class="teklif-mesaj" data-alan="teklif-mesaj"></span>
            </form>
        @else
            <a class="btn btn-dolu" href="{{ route('giris') }}">Teklif için giriş</a>
        @endauth
    @endif

    @if ($ilan['durum'] === 'acik_artirma')
        <div class="teklif-sayisi">Teklifler: {{ $ilan['teklifSayisi'] }}</div>
    @elseif ($ilan['durum'] === 'kapandi')
        <div class="teklif-sayisi">Kazanan: {{ $ilan['sonTeklifSahibi'] ?? '—' }}</div>
    @endif
</article>
