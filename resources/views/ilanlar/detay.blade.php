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
                @elseif ($ozet['durum'] === 'dusuyor')
                    <div class="stok-no">Stok No: {{ $ozet['id'] }}</div>
                @endif
                <h1>{{ $ozet['baslik'] }}</h1>
                @if ($ozet['altBaslik'])
                    <div class="alt">{{ $ozet['altBaslik'] }}</div>
                @endif
                @if ($ilan->aciklama)
                    <div class="detay-aciklama"><p>{{ $ilan->aciklama }}</p></div>
                @endif
                <span class="rozet {{ $ozet['tabanaUlasti'] ? 'rozet-taban' : '' }}">{{ $ozet['durumEtiket'] }}</span>

                <div class="detay-kutu">
                    <div class="etiket" data-alan="fiyat-etiket">
                        {{ $ozet['durum'] === 'dusuyor' ? 'Düşen fiyat' : ($ozet['durum'] === 'kapandi' ? 'Kapanış fiyatı' : 'Güncel teklif') }}
                    </div>
                    <div class="fiyat" data-alan="fiyat" data-deger="{{ $ozet['guncelFiyat'] }}">{{ $ozet['guncelFiyatBicim'] }}</div>
                    @if ($ozet['durum'] === 'dusuyor')<span class="dususok" aria-hidden="true"><i></i><i></i><i></i></span>@endif

                    @if ($ozet['durum'] === 'acik_artirma')
                        <div class="etiket" style="margin-top:.4rem">{{ $ozet['teklifSayisi'] }} teklif</div>
                    @endif

                    @if ($ozet['durum'] !== 'kapandi')
                        <p class="sayac-etiket" data-alan="sayac-etiket" @if($ozet['tabanaUlasti']) style="display:none" @endif>
                            {{ $ozet['durum'] === 'dusuyor' ? 'Sonraki düşüşe' : 'Bitişe kalan' }}
                        </p>
                        <p class="sayac" role="timer" data-alan="sayac" @if($ozet['tabanaUlasti']) style="display:none" @endif>--:--</p>

                        @auth
                            <div class="onde-bilgi {{ $ozet['benimDurum'] === 'gecildi' ? 'onde-kirmizi' : 'onde-yesil' }}" data-alan="onde" @unless($ozet['benimDurum']) hidden @endunless>{{ $ozet['benimDurum'] === 'gecildi' ? '★ Teklifiniz geçilmiştir' : ($ozet['benimDurum'] === 'onde' ? '★ Şu an en yüksek teklife sahipsiniz' : '') }}</div>
                            <div class="benim-max" data-alan="benim-max" @unless($ozet['benimMax']) hidden @endunless>Maksimum teklifiniz: <strong data-alan="benim-max-tutar">{{ $ozet['benimMaxBicim'] }}</strong></div>
                            <form class="teklif-form" data-alan="teklif-form">
                                @csrf
                                <input type="hidden" name="ilan_id" value="{{ $ozet['id'] }}">
                                <div class="pey-kutu" @if($ozet['durum'] === 'dusuyor') style="display:none" @endif>
                                    <button type="button" class="pey-btn" data-alan="pey-eksi" tabindex="-1" aria-label="Azalt">−</button>
                                    <input type="number" name="miktar" step="1" readonly inputmode="none"
                                           min="{{ $ozet['minTeklif'] }}" value="{{ $ozet['minTeklif'] }}"
                                           data-alan="miktar" required title="+ / − ile ayarlayın">
                                    <button type="button" class="pey-btn" data-alan="pey-arti" tabindex="-1" aria-label="Artır">+</button>
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
    <script src="{{ asset('assets/sayac.js') }}?v={{ filemtime(public_path('assets/sayac.js')) }}"></script>
@endpush
