@extends('layouts.yonetim')

@section('baslik', 'Ürün Kartları')

@section('content')
    <main class="yonetim">
        <div class="yonetim-ust">
            <h1>Ürün Kartları — Ana Sayfa Alt</h1>
            <div class="yonetim-eylem">
                <button type="submit" form="kart-form" class="btn btn-dolu">Kaydet</button>
            </div>
        </div>

        <p class="alt-not" style="margin-bottom:1rem">
            Ana sayfanın en altında görünecek ürün kartları için lot seç (sıraya göre, satır başına 4'erli dizilir).
            Şu an <strong>{{ $secili }}</strong> lot seçili. Hiç seçmezsen bu bölüm gizli kalır.
        </p>

        <form method="post" action="{{ route('yonetim.kartlar.kaydet') }}" id="kart-form">
            @csrf
            <div class="carusel-izgara">
                @foreach ($ilanlar as $ilan)
                    <label class="carusel-secim {{ $ilan->kart ? 'secili' : '' }}">
                        <input type="checkbox" name="secili[]" value="{{ $ilan->id }}" @checked($ilan->kart)>
                        <span class="carusel-gorsel">
                            @if ($ilan->gorsel_url)<img src="{{ $ilan->gorsel_url }}" alt="" loading="lazy">@endif
                        </span>
                        <span class="carusel-bilgi">
                            <strong>@if ($ilan->lot_no)Lot {{ $ilan->lot_no }} · @endif{{ $ilan->baslik }}</strong>
                            <small>{{ $ilan->alt_baslik }}</small>
                        </span>
                        <input type="number" class="carusel-sira" name="sira[{{ $ilan->id }}]" min="1"
                               value="{{ $ilan->kart_sira }}" placeholder="Sıra" title="Sıra numarası"
                               onclick="event.stopPropagation();">
                    </label>
                @endforeach
            </div>
            <div style="margin-top:1.4rem">
                <button type="submit" class="btn btn-dolu">Kaydet</button>
            </div>
        </form>
    </main>
    <script>
        document.querySelectorAll('.carusel-secim input[type="checkbox"]').forEach(function (cb) {
            cb.addEventListener('change', function () {
                cb.closest('.carusel-secim').classList.toggle('secili', cb.checked);
            });
        });
    </script>
@endsection
