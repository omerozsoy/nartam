@extends('layouts.yonetim')

@section('baslik', 'Carusel')

@section('content')
    <main class="yonetim">
        <div class="yonetim-ust">
            <h1>Carusel — Ana Sayfa Alt (Coverflow)</h1>
            <div class="yonetim-eylem">
                <button type="submit" form="carusel-form" class="btn btn-dolu">Kaydet</button>
            </div>
        </div>

        <p class="alt-not" style="margin-bottom:1rem">
            Ana sayfanın altındaki 3B kayan carusel'de görünecek lotları seç. Sıra numarası ile diz.
            Şu an <strong>{{ $secili }}</strong> lot seçili. Hiç seçmezsen aktif müzayedenin Açık Artırma lotları otomatik gösterilir.
        </p>

        <form method="post" action="{{ route('yonetim.carusel.kaydet') }}" id="carusel-form">
            @csrf
            <div class="carusel-izgara">
                @foreach ($ilanlar as $ilan)
                    <label class="carusel-secim {{ $ilan->coverflow ? 'secili' : '' }}">
                        <input type="checkbox" name="secili[]" value="{{ $ilan->id }}" @checked($ilan->coverflow)>
                        <span class="carusel-gorsel">
                            @if ($ilan->gorsel_url)<img src="{{ $ilan->gorsel_url }}" alt="" loading="lazy">@endif
                        </span>
                        <span class="carusel-bilgi">
                            <strong>@if ($ilan->lot_no)Lot {{ $ilan->lot_no }} · @endif{{ $ilan->baslik }}</strong>
                            <small>{{ $ilan->alt_baslik }}</small>
                        </span>
                        <input type="number" class="carusel-sira" name="sira[{{ $ilan->id }}]" min="1"
                               value="{{ $ilan->coverflow_sira }}" placeholder="Sıra" title="Sıra numarası"
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
