@if (($vitrin ?? collect())->isNotEmpty())
    <section class="vitrin" data-alan="slider">
        <div class="swiper vitrin-swiper">
            <div class="swiper-wrapper">
                @foreach ($vitrin as $ilan)
                    <a class="swiper-slide {{ $ilan['gorselUrl'] ? '' : 'bos' }}" href="{{ route('ilan.goster', $ilan['id']) }}">
                        @if ($ilan['gorselUrl'])
                            <img src="{{ $ilan['gorselUrl'] }}" alt="{{ $ilan['baslik'] }}">
                        @else
                            <span class="vitrin-harf">{{ mb_strtoupper(mb_substr($ilan['baslik'], 0, 1)) }}</span>
                        @endif
                        <div class="vitrin-bilgi">
                            @if ($ilan['lotNo'])<span class="vitrin-lot">Lot {{ $ilan['lotNo'] }}</span>@endif
                            <strong>{{ $ilan['baslik'] }}</strong>
                            <span class="vitrin-fiyat">{{ $ilan['guncelFiyatBicim'] }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </section>
@endif
