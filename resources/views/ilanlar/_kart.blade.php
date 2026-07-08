@php($detayUrl = route('ilan.goster', $ilan['id']))
<article
    class="lot durum-{{ $ilan['durum'] }}"
    data-id="{{ $ilan['id'] }}"
    data-durum="{{ $ilan['durum'] }}"
    @if ($ilan['bitisTs']) data-bitis="{{ $ilan['bitisTs'] }}" @endif
    @if ($ilan['sonrakiDususTs']) data-sonraki-dusus="{{ $ilan['sonrakiDususTs'] }}" @endif
    @if ($ilan['baslangicTs']) data-baslangic="{{ $ilan['baslangicTs'] }}" @endif
    data-min="{{ $ilan['minTeklif'] }}"
    data-ara="{{ mb_strtolower(trim(($ilan['baslik'] ?? '') . ' ' . ($ilan['altBaslik'] ?? '') . ' ' . ($ilan['lotNo'] ? 'lot ' . $ilan['lotNo'] : '') . ' ' . $ilan['id'])) }}"
>
    <a href="{{ $detayUrl }}" class="lot-gorsel {{ $ilan['gorselUrl'] ? '' : 'bos' }}">
        @if ($ilan['gorselUrl'])
            <img src="{{ $ilan['gorselUrl'] }}" alt="{{ $ilan['baslik'] }}">
        @else
            {{ mb_strtoupper(mb_substr($ilan['baslik'], 0, 1)) }}
            <small>Yeni Müzayede</small>
        @endif
    </a>

    @if ($ilan['durum'] !== 'acik_artirma')
        <span class="rozet {{ $ilan['tabanaUlasti'] ? 'rozet-taban' : '' }}">{{ $ilan['durumEtiket'] }}</span>
    @endif

    @if ($ilan['durum'] === 'yakinda')
        <p class="sayac-etiket">Başlangıca kalan</p>
        <p class="sayac" role="timer" data-alan="sayac">--:--</p>
        <p class="yakinda-not">{{ $ilan['baslangicBicim'] }}</p>
    @elseif ($ilan['durum'] !== 'kapandi')
        <p class="sayac" role="timer" data-alan="sayac" @if($ilan['tabanaUlasti']) style="display:none" @endif>--:--</p>
    @endif

    @if ($ilan['lotNo'])
        <div class="lot-no">Lot {{ $ilan['lotNo'] }}</div>
    @elseif ($ilan['durum'] === 'dusuyor')
        <div class="stok-no">Stok No: {{ $ilan['id'] }}</div>
    @endif

    <h2 class="lot-baslik"><a href="{{ $detayUrl }}">{{ $ilan['baslik'] }}</a></h2>
    <div class="lot-alt">{{ $ilan['altBaslik'] }}</div>

    <div class="lot-satir fiyat-satir">
        @if ($ilan['durum'] === 'dusuyor')
            <div class="baslangic-satir" data-alan="baslangic-satir" @unless($ilan['dususYuzde'] > 0) hidden @endunless>
                <span class="baslangic-fiyat">{{ $ilan['baslangicFiyatiBicim'] }}</span>
                <span class="dusus-yuzde" data-alan="dusus-yuzde">%{{ $ilan['dususYuzde'] }} ↓</span>
            </div>
        @endif
        <div class="fiyat" data-alan="fiyat" data-deger="{{ $ilan['guncelFiyat'] }}">{{ $ilan['guncelFiyatBicim'] }}</div>
        @if ($ilan['durum'] === 'dusuyor')<span class="dususok" aria-hidden="true"><i></i><i></i><i></i></span>@endif
    </div>

    @if (in_array($ilan['durum'], ['acik_artirma', 'dusuyor'], true))
        @auth
            <div class="onde-bilgi {{ $ilan['benimDurum'] === 'gecildi' ? 'onde-kirmizi' : 'onde-yesil' }}" data-alan="onde" @unless($ilan['benimDurum']) hidden @endunless>{{ $ilan['benimDurum'] === 'gecildi' ? '★ Teklifiniz geçilmiştir' : ($ilan['benimDurum'] === 'onde' ? '★ Şu an en yüksek teklife sahipsiniz' : '') }}</div>
            <div class="benim-max" data-alan="benim-max" @unless($ilan['benimMax']) hidden @endunless>Maksimum teklifiniz: <strong data-alan="benim-max-tutar">{{ $ilan['benimMaxBicim'] }}</strong></div>
            <form class="teklif-form" data-alan="teklif-form">
                @csrf
                <input type="hidden" name="ilan_id" value="{{ $ilan['id'] }}">
                <div class="pey-kutu" @if($ilan['durum'] === 'dusuyor') style="display:none" @endif>
                    <button type="button" class="pey-btn" data-alan="pey-eksi" tabindex="-1" aria-label="Azalt">−</button>
                    <input type="number" name="miktar" step="1" readonly inputmode="none"
                           min="{{ $ilan['minTeklif'] }}" value="{{ $ilan['minTeklif'] }}"
                           data-alan="miktar" required title="+ / − ile ayarlayın">
                    <button type="button" class="pey-btn" data-alan="pey-arti" tabindex="-1" aria-label="Artır">+</button>
                </div>
                <div class="lot-butonlar">
                    <button type="submit" class="btn">
                        {{ $ilan['durum'] === 'dusuyor' ? 'Bu Fiyattan Teklif Ver' : 'Teklif Ver' }}
                    </button>
                    <button type="button" class="btn takip-btn {{ $ilan['takipEdiliyor'] ? 'takip-aktif' : '' }}"
                            data-alan="takip" data-id="{{ $ilan['id'] }}" aria-pressed="{{ $ilan['takipEdiliyor'] ? 'true' : 'false' }}">
                        {{ $ilan['takipEdiliyor'] ? 'Takip Ediliyor' : 'Takip Et' }}
                    </button>
                </div>
                <span class="teklif-mesaj" data-alan="teklif-mesaj"></span>
            </form>
        @else
            <a class="btn btn-dolu" href="{{ route('giris') }}">Teklif için giriş</a>
        @endauth
    @elseif ($ilan['durum'] === 'yakinda')
        @auth
            <button type="button" class="btn takip-btn {{ $ilan['takipEdiliyor'] ? 'takip-aktif' : '' }}"
                    data-alan="takip" data-id="{{ $ilan['id'] }}" aria-pressed="{{ $ilan['takipEdiliyor'] ? 'true' : 'false' }}">
                {{ $ilan['takipEdiliyor'] ? 'Takip Ediliyor' : 'Takip Et' }}
            </button>
        @else
            <a class="btn" href="{{ route('giris') }}">Takip için giriş</a>
        @endauth
    @endif

    @if ($ilan['durum'] === 'acik_artirma')
        <div class="teklif-sayisi">Teklifler: <span data-alan="teklif-sayisi">{{ $ilan['teklifSayisi'] }}</span></div>
    @elseif ($ilan['durum'] === 'kapandi')
        <div class="teklif-sayisi">Kazanan: {{ $ilan['sonTeklifSahibi'] ?? '—' }}</div>
    @endif
</article>
