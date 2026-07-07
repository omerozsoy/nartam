@extends('layouts.app')

@section('baslik', 'İletişim')

@section('content')
    <div class="kap">
        <div class="sayfa">
            <h1>İletişim</h1>

            <div class="iletisim-izgara">
                <div class="iletisim-bilgi">
                    @if ($a['iletisim_adres'])
                        <div class="iletisim-satir">
                            <span class="iletisim-etiket">Adres</span>
                            <p>{!! nl2br(e($a['iletisim_adres'])) !!}</p>
                        </div>
                    @endif
                    @if ($a['iletisim_telefon'])
                        <div class="iletisim-satir">
                            <span class="iletisim-etiket">Telefon</span>
                            <p><a href="tel:{{ preg_replace('/\s+/', '', $a['iletisim_telefon']) }}">{{ $a['iletisim_telefon'] }}</a></p>
                        </div>
                    @endif
                    @if ($a['iletisim_eposta'])
                        <div class="iletisim-satir">
                            <span class="iletisim-etiket">E-posta</span>
                            <p><a href="mailto:{{ $a['iletisim_eposta'] }}">{{ $a['iletisim_eposta'] }}</a></p>
                        </div>
                    @endif
                    @if ($a['iletisim_saatler'])
                        <div class="iletisim-satir">
                            <span class="iletisim-etiket">Çalışma Saatleri</span>
                            <p>{!! nl2br(e($a['iletisim_saatler'])) !!}</p>
                        </div>
                    @endif

                    @php($sosyal = array_filter([
                        'Instagram' => $a['sosyal_instagram'],
                        'Facebook' => $a['sosyal_facebook'],
                        'X' => $a['sosyal_twitter'],
                        'WhatsApp' => $a['sosyal_whatsapp'],
                    ]))
                    @if ($sosyal)
                        <div class="iletisim-satir">
                            <span class="iletisim-etiket">Sosyal Medya</span>
                            <div class="sosyal-baglar">
                                @foreach ($sosyal as $ad => $bag)
                                    <a href="{{ $bag }}" target="_blank" rel="noopener">{{ $ad }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($a['iletisim_metin'])
                        <div class="iletisim-metin">{!! nl2br(e($a['iletisim_metin'])) !!}</div>
                    @endif
                </div>

                @if ($a['iletisim_harita'])
                    <div class="iletisim-harita">{!! $a['iletisim_harita'] !!}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
