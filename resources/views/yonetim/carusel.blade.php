@extends('layouts.yonetim')

@section('baslik', 'Carousel')

@section('content')
    <main class="yonetim">
        <div class="yonetim-ust">
            <h1>Carousel — Ana Sayfa Hero</h1>
            <div class="yonetim-eylem">
                <button type="submit" form="carusel-form" class="btn btn-dolu">Kaydet</button>
            </div>
        </div>

        <p class="alt-not" style="margin-bottom:1rem">
            Ana sayfanın üstündeki büyük hero slider'ında görünecek lotları seç.
            Şu an <strong>{{ $secili }}</strong> lot seçili. Hiç seçmezsen hero gizli kalır.
        </p>

        <form method="post" action="{{ route('yonetim.carusel.kaydet') }}" id="carusel-form">
            @csrf
            <div class="carusel-izgara">
                @foreach ($ilanlar as $ilan)
                    <label class="carusel-secim {{ $ilan->carusel ? 'secili' : '' }}">
                        <input type="checkbox" name="secili[]" value="{{ $ilan->id }}" @checked($ilan->carusel)>
                        <span class="carusel-gorsel">
                            @if ($ilan->gorsel_url)<img src="{{ $ilan->gorsel_url }}" alt="" loading="lazy">@endif
                        </span>
                        <span class="carusel-bilgi">
                            <strong>@if ($ilan->lot_no)Lot {{ $ilan->lot_no }} · @endif{{ $ilan->baslik }}</strong>
                            <small>{{ $ilan->alt_baslik }}</small>
                        </span>
                    </label>
                @endforeach
            </div>
            <div style="margin-top:1.4rem">
                <button type="submit" class="btn btn-dolu">Kaydet</button>
            </div>
        </form>
    </main>
    <script>
        document.querySelectorAll('.carusel-secim input').forEach(function (cb) {
            cb.addEventListener('change', function () {
                cb.closest('.carusel-secim').classList.toggle('secili', cb.checked);
            });
        });
    </script>
@endsection
