@extends('layouts.yonetim')

@section('baslik', 'Slider')

@section('content')
    <main class="yonetim">
        <div class="yonetim-ust">
            <h1>Slider — Ana Sayfa Üst Hero</h1>
            <div class="yonetim-eylem">
                <button type="submit" form="slider-form" class="btn btn-dolu">Kaydet</button>
            </div>
        </div>

        <p class="alt-not" style="margin-bottom:1rem">
            Ana sayfanın üstündeki büyük (bölünmüş) slider'da görünecek lotları seç. Her lot için sıra,
            metin tarafı (sol/sağ) ve panel arka plan rengini ayarla.
            Şu an <strong>{{ $secili }}</strong> lot seçili. Hiç seçmezsen slider gizli kalır.
        </p>

        <form method="post" action="{{ route('yonetim.slider.kaydet') }}" id="slider-form">
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
                        <input type="number" class="carusel-sira" name="sira[{{ $ilan->id }}]" min="1"
                               value="{{ $ilan->carusel_sira }}" placeholder="Sıra" title="Sıra numarası"
                               onclick="event.stopPropagation();">
                        @php($sag = str_contains($ilan->carusel_konum ?? 'sol', 'sag'))
                        <select class="carusel-konum" name="konum[{{ $ilan->id }}]" title="Metin paneli tarafı" onclick="event.stopPropagation();">
                            <option value="sol" @selected(! $sag)>Metin Sol</option>
                            <option value="sag" @selected($sag)>Metin Sağ</option>
                        </select>
                        <input type="color" class="carusel-arka" name="arka[{{ $ilan->id }}]"
                               value="{{ $ilan->carusel_arka ?: '#efe9dd' }}" title="Panel arka plan rengi"
                               onclick="event.stopPropagation();">
                    </label>
                @endforeach
            </div>
            <div style="margin-top:1.4rem">
                <button type="submit" class="btn btn-dolu">Kaydet</button>
            </div>
        </form>
    </main>
    @include('yonetim._secim_script')
@endsection
