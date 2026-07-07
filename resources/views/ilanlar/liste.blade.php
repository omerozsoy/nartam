@extends('layouts.app')

@section('baslik', 'Müzayede')

@section('content')
    <div class="kap">
        @php($vitrin = ($gruplar['acik_artirma'] ?? collect())->take(10))
        @if ($vitrin->isNotEmpty())
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

        <div class="arama" data-alan="arama">
            <input type="search" class="arama-girdi" data-alan="arama-girdi"
                   placeholder="Sanatçı, eser adı veya lot no ara…" autocomplete="off" spellcheck="false">
            <ul class="arama-oneri" data-alan="arama-oneri" hidden></ul>
        </div>

        <main id="lotlar">
            @php($bolumler = ['acik_artirma' => 'Açık Artırma', 'dusuyor' => 'Açık Eksiltme', 'kapandi' => 'Kapandı'])
            @foreach ($bolumler as $durum => $bolumBaslik)
                @php($grup = $gruplar[$durum] ?? collect())
                @if ($grup->isNotEmpty())
                    <section class="lot-bolum">
                        <h2 class="bolum-baslik">
                            {{ $bolumBaslik }}
                            <span>{{ $grup->count() }} lot</span>
                        </h2>
                        <div class="lot-izgara">
                            @foreach ($grup as $ilan)
                                @include('ilanlar._kart', ['ilan' => $ilan])
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
            <p class="hesap-bos arama-yok" data-alan="arama-yok" hidden>Aramanızla eşleşen eser bulunamadı.</p>
        </main>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swiper === 'undefined' || !document.querySelector('.vitrin-swiper')) {
                return;
            }
            new Swiper('.vitrin-swiper', {
                effect: 'coverflow',
                grabCursor: true,
                centeredSlides: true,
                slidesPerView: 'auto',
                loop: true,
                coverflowEffect: { rotate: 20, stretch: 0, depth: 350, modifier: 1, slideShadows: true },
                autoplay: { delay: 3500, disableOnInteraction: false },
                pagination: { el: '.vitrin-swiper .swiper-pagination', clickable: true },
                navigation: {
                    nextEl: '.vitrin-swiper .swiper-button-next',
                    prevEl: '.vitrin-swiper .swiper-button-prev',
                },
            });
        });
    </script>
    <script src="{{ asset('assets/sayac.js') }}?v={{ filemtime(public_path('assets/sayac.js')) }}"></script>
@endpush
