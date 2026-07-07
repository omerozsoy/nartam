@extends('layouts.app')

@section('baslik', 'Müzayede')

@section('content')
    <div class="kap">
        @php($vitrin = ($gruplar['acik_artirma'] ?? collect())->take(8))
        @if ($vitrin->isNotEmpty())
            <section class="slider" data-alan="slider">
                <div class="slider-govde">
                    <ul class="slider-ray" data-alan="slider-ray">
                        @foreach ($vitrin as $ilan)
                            <li class="slider-slayt">
                                <a href="{{ route('ilan.goster', $ilan['id']) }}" class="slider-gorsel {{ $ilan['gorselUrl'] ? '' : 'bos' }}">
                                    @if ($ilan['gorselUrl'])
                                        <img src="{{ $ilan['gorselUrl'] }}" alt="{{ $ilan['baslik'] }}">
                                    @else
                                        {{ mb_strtoupper(mb_substr($ilan['baslik'], 0, 1)) }}
                                    @endif
                                </a>
                                <div class="slider-bilgi">
                                    <div class="slider-etiket">Açık Artırma</div>
                                    @if ($ilan['lotNo'])<div class="slider-lot">Lot {{ $ilan['lotNo'] }}</div>@endif
                                    <h2>{{ $ilan['baslik'] }}</h2>
                                    @if ($ilan['altBaslik'])<div class="slider-alt">{{ $ilan['altBaslik'] }}</div>@endif
                                    <div class="slider-fiyat">{{ $ilan['guncelFiyatBicim'] }}</div>
                                    <a class="btn btn-dolu" href="{{ route('ilan.goster', $ilan['id']) }}">İncele ve Teklif Ver</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="slider-ok slider-onceki" data-alan="slider-onceki" aria-label="Önceki"></button>
                <button type="button" class="slider-ok slider-sonraki" data-alan="slider-sonraki" aria-label="Sonraki"></button>
                <div class="slider-nokta" data-alan="slider-nokta"></div>
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
    <script src="{{ asset('assets/sayac.js') }}?v={{ filemtime(public_path('assets/sayac.js')) }}"></script>
@endpush
