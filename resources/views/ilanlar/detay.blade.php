@extends('layouts.app')

@section('baslik', $ozet['baslik'])

@section('content')
    <div class="kap">
        <a class="geri-bag" href="{{ route('ilanlar.liste') }}">‹ Tüm lotlar</a>

        <div class="lot-detay durum-{{ $ozet['durum'] }}"
             data-id="{{ $ozet['id'] }}"
             data-durum="{{ $ozet['durum'] }}"
             @if ($ozet['bitisTs']) data-bitis="{{ $ozet['bitisTs'] }}" @endif
             @if ($ozet['sonrakiDususTs']) data-sonraki-dusus="{{ $ozet['sonrakiDususTs'] }}" @endif
             data-min="{{ $ozet['minTeklif'] }}">

            <div class="detay-gorsel {{ $ozet['gorselUrl'] ? '' : 'lot-gorsel bos' }}">
                @if ($ozet['gorselUrl'])
                    <img src="{{ $ozet['gorselUrl'] }}" alt="{{ $ozet['baslik'] }}">
                @else
                    {{ mb_strtoupper(mb_substr($ozet['baslik'], 0, 1)) }}
                    <small>görsel yok</small>
                @endif
            </div>

            <div class="detay-bilgi">
                @if ($ozet['lotNo'])
                    <div class="lot-no">LOT {{ $ozet['lotNo'] }}</div>
                @endif
                <h1>{{ $ozet['baslik'] }}</h1>
                @if ($ozet['altBaslik'])
                    <div class="alt">{{ $ozet['altBaslik'] }}</div>
                @endif
                <span class="rozet">{{ $ozet['durumEtiket'] }}</span>

                <div class="detay-kutu">
                    <div class="etiket" data-alan="fiyat-etiket">
                        {{ $ozet['durum'] === 'dusuyor' ? 'Düşen fiyat' : ($ozet['durum'] === 'kapandi' ? 'Kapanış fiyatı' : 'Güncel teklif') }}
                    </div>
                    <div class="fiyat" data-alan="fiyat" data-deger="{{ $ozet['guncelFiyat'] }}">{{ $ozet['guncelFiyatBicim'] }}</div>
                    @if ($ozet['durum'] === 'dusuyor')<i class="dususok" aria-hidden="true"></i>@endif

                    @if ($ozet['durum'] === 'acik_artirma')
                        <div class="etiket" style="margin-top:.4rem">{{ $ozet['teklifSayisi'] }} teklif</div>
                    @endif

                    @if ($ozet['durum'] !== 'kapandi')
                        <p class="sayac-etiket" data-alan="sayac-etiket">
                            {{ $ozet['durum'] === 'dusuyor' ? 'Sonraki düşüşe' : 'Bitişe kalan' }}
                        </p>
                        <p class="sayac" role="timer" data-alan="sayac">--:--</p>

                        @auth
                            <div class="onde-bilgi" data-alan="onde" @unless(auth()->id() === $ozet['liderId']) hidden @endunless>★ Şu an öndesiniz</div>
                            <form class="teklif-form" data-alan="teklif-form">
                                @csrf
                                <input type="hidden" name="ilan_id" value="{{ $ozet['id'] }}">
                                <div class="pey-kutu">
                                    <button type="button" class="pey-btn" data-alan="pey-eksi" tabindex="-1" aria-label="Azalt"><i class="ok asagi"></i></button>
                                    <input type="number" name="miktar" step="1" readonly inputmode="none"
                                           min="{{ $ozet['minTeklif'] }}" value="{{ $ozet['minTeklif'] }}"
                                           data-alan="miktar" required title="+ / − ile ayarlayın">
                                    <button type="button" class="pey-btn" data-alan="pey-arti" tabindex="-1" aria-label="Artır"><i class="ok yukari"></i></button>
                                </div>
                                <button type="submit" class="btn btn-dolu">
                                    {{ $ozet['durum'] === 'dusuyor' ? 'Bu Fiyattan 24 Saatlik Müzayedeyi Başlat' : 'Teklif Ver' }}
                                </button>
                                <span class="teklif-mesaj" data-alan="teklif-mesaj"></span>
                            </form>
                            <p class="alt-not" style="margin-top:.6rem">Girdiğiniz tutar <strong>gizli maksimumunuzdur</strong>; başkaları teklif verdikçe sistem, bu tutara kadar sizin adınıza otomatik pey verir.</p>
                        @else
                            <a class="btn btn-dolu" href="{{ route('giris') }}" style="margin-top:1rem">Teklif için giriş</a>
                        @endauth
                    @else
                        <p class="sayac-etiket">Kazanan: {{ $ozet['sonTeklifSahibi'] ?? '—' }}</p>
                    @endif
                </div>

                @if ($ilan->aciklama)
                    <div class="detay-aciklama">
                        <h3>Açıklama</h3>
                        <p>{{ $ilan->aciklama }}</p>
                    </div>
                @endif

                @if ($teklifler->isNotEmpty())
                    <div class="detay-aciklama">
                        <h3>Teklif Geçmişi</h3>
                        <table class="teklif-tablo">
                            <thead>
                            <tr><th>Katılımcı</th><th>Tutar</th><th>Zaman</th></tr>
                            </thead>
                            <tbody>
                            @foreach ($teklifler as $teklif)
                                <tr>
                                    <td>{{ \App\Support\Ad::gizle($teklif->kullanici->name ?? '—') }}</td>
                                    <td>{{ number_format($teklif->miktar, 0, ',', '.') }} ₺</td>
                                    <td>{{ $teklif->zaman->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/sayac.js') }}"></script>
@endpush
