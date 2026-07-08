@if (($hero ?? collect())->isNotEmpty())
    <section class="hero">
        <div class="swiper hero-swiper">
            <div class="swiper-wrapper">
                @foreach ($hero as $ilan)
                    @php($sag = str_contains($ilan['caruselKonum'] ?? 'sol', 'sag'))
                    <div class="swiper-slide hero-slide {{ $sag ? 'panel-sag' : 'panel-sol' }}">
                        <div class="hero-panel" style="background:{{ $ilan['caruselArka'] ?? '#efe9dd' }}">
                            <div class="hero-metin">
                                <span class="hero-etiket">{{ $ilan['durumEtiket'] }}@if ($ilan['lotNo']) · Lot {{ $ilan['lotNo'] }}@endif</span>
                                <h2>{{ $ilan['baslik'] }}</h2>
                                @if ($ilan['altBaslik'])<div class="hero-alt">{{ $ilan['altBaslik'] }}</div>@endif
                                <div class="hero-fiyat">{{ $ilan['guncelFiyatBicim'] }}</div>
                                <a class="btn btn-dolu" href="{{ route('ilan.goster', $ilan['id']) }}">İncele</a>
                            </div>
                        </div>
                        <a class="hero-gorsel {{ $ilan['gorselUrl'] ? '' : 'bos' }}" href="{{ route('ilan.goster', $ilan['id']) }}">
                            @if ($ilan['gorselUrl'])<img src="{{ $ilan['gorselUrl'] }}" alt="{{ $ilan['baslik'] }}">@endif
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </section>
@endif
