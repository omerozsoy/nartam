@extends('layouts.app')

@section('baslik', 'Hesabım')

@php
    $etiketler = ['onde' => 'Önde', 'geride' => 'Geçildiniz', 'kazandi' => 'Kazandınız', 'kaybetti' => 'Kaybettiniz'];
@endphp

@section('content')
    <div class="kap hesap" id="hesap-panel">
        <h1 class="hesap-baslik">Hesabım</h1>
        @include('hesap._nav')

        {{-- Kazandıklarım --}}
        @if ($kazandiklarim->isNotEmpty())
            <h1 class="hesap-baslik">Kazandıklarım</h1>
            <table class="hesap-tablo">
                <tbody>
                @foreach ($kazandiklarim as $s)
                    <tr>
                        <td class="h-gorsel">
                            @if ($s['gorselUrl'])<img src="{{ $s['gorselUrl'] }}" alt="{{ $s['baslik'] }}">@else<span class="h-bos">{{ mb_strtoupper(mb_substr($s['baslik'], 0, 1)) }}</span>@endif
                        </td>
                        <td>
                            @if ($s['lotNo'])<div class="lot-no">LOT {{ $s['lotNo'] }}</div>@endif
                            <div class="h-ad">{{ $s['baslik'] }}</div>
                        </td>
                        <td><span class="durum-etiket d-kazandi">Kazandınız</span></td>
                        <td class="h-tutar">{{ $s['benimTeklifimBicim'] }}</td>
                        <td><a class="btn btn-dolu" href="{{ route('odeme', $s['id']) }}">Ödeme Yap</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        {{-- Pey verdiğim eserler --}}
        <h1 class="hesap-baslik">Pey Verdiğim Eserler</h1>

        @if ($diger->isEmpty() && $kazandiklarim->isEmpty())
            <p class="hesap-bos">Henüz pey vermediniz. <a href="{{ route('ilanlar.liste') }}">Eserlere göz atın →</a></p>
        @elseif ($diger->isEmpty())
            <p class="hesap-bos">Devam eden başka teklifiniz yok.</p>
        @else
            <table class="hesap-tablo">
                <thead>
                <tr><th></th><th>Eser</th><th>Durum</th><th>Benim Teklifim</th><th>Güncel Fiyat</th><th></th></tr>
                </thead>
                <tbody>
                @foreach ($diger as $s)
                    <tr data-id="{{ $s['id'] }}" data-durumum="{{ $s['durumum'] }}">
                        <td class="h-gorsel">
                            @if ($s['gorselUrl'])<img src="{{ $s['gorselUrl'] }}" alt="{{ $s['baslik'] }}">@else<span class="h-bos">{{ mb_strtoupper(mb_substr($s['baslik'], 0, 1)) }}</span>@endif
                        </td>
                        <td>
                            @if ($s['lotNo'])<div class="lot-no">LOT {{ $s['lotNo'] }}</div>@endif
                            <div class="h-ad">{{ $s['baslik'] }}</div>
                            @if ($s['altBaslik'])<div class="lot-alt">{{ $s['altBaslik'] }}</div>@endif
                        </td>
                        <td><span class="durum-etiket d-{{ $s['durumum'] }}" data-alan="h-durum">{{ $etiketler[$s['durumum']] }}</span></td>
                        <td class="h-tutar">{{ $s['benimTeklifimBicim'] }}</td>
                        <td class="h-tutar" data-alan="h-fiyat">{{ $s['guncelFiyatBicim'] }}</td>
                        <td><a class="btn" href="{{ route('ilan.goster', $s['id']) }}">İncele</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/panel.js') }}?v={{ filemtime(public_path('assets/panel.js')) }}"></script>
@endpush
