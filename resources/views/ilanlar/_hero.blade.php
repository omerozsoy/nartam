@if (($hero ?? collect())->isNotEmpty())
    <section class="hero">
        <div class="swiper hero-swiper">
            <div class="swiper-wrapper">
                @foreach ($hero as $ilan)
                    <div class="swiper-slide hero-slide konum-{{ $ilan['caruselKonum'] ?? 'sol-alt' }}">
                        @if ($ilan['gorselUrl'])
                            <div class="hero-bg" style="background-image:url('{{ $ilan['gorselUrl'] }}')"></div>
                            <img class="hero-on" src="{{ $ilan['gorselUrl'] }}" alt="{{ $ilan['baslik'] }}">
                        @endif
                        <div class="hero-ic">
                            <div class="hero-kart">
                                <span class="hero-etiket">{{ $ilan['durumEtiket'] }}@if ($ilan['lotNo']) · Lot {{ $ilan['lotNo'] }}@endif</span>
                                <h2>{{ $ilan['baslik'] }}</h2>
                                @if ($ilan['altBaslik'])<div class="hero-alt">{{ $ilan['altBaslik'] }}</div>@endif
                                <div class="hero-fiyat">{{ $ilan['guncelFiyatBicim'] }}</div>
                                <a class="btn btn-dolu" href="{{ route('ilan.goster', $ilan['id']) }}">İncele</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </section>
@endif
