@extends('layouts.app')

@section('baslik', 'Hesabım')

@php
    $etiketler = ['onde' => 'Önde', 'geride' => 'Geçildiniz', 'kazandi' => 'Kazandınız', 'kaybetti' => 'Kaybettiniz'];
@endphp

@section('content')
    <div class="kap hesap">
        <h1 class="hesap-baslik">Pey Verdiğim Eserler</h1>

        @if ($satirlar->isEmpty())
            <p class="hesap-bos">Henüz pey vermediniz. <a href="{{ route('ilanlar.liste') }}">Eserlere göz atın →</a></p>
        @else
            <table class="hesap-tablo">
                <thead>
                <tr>
                    <th></th>
                    <th>Eser</th>
                    <th>Durum</th>
                    <th>Benim Teklifim</th>
                    <th>Güncel Fiyat</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($satirlar as $s)
                    <tr>
                        <td class="h-gorsel">
                            @if ($s['gorselUrl'])
                                <img src="{{ $s['gorselUrl'] }}" alt="{{ $s['baslik'] }}">
                            @else
                                <span class="h-bos">{{ mb_strtoupper(mb_substr($s['baslik'], 0, 1)) }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($s['lotNo'])<div class="lot-no">LOT {{ $s['lotNo'] }}</div>@endif
                            <div class="h-ad">{{ $s['baslik'] }}</div>
                            @if ($s['altBaslik'])<div class="lot-alt">{{ $s['altBaslik'] }}</div>@endif
                        </td>
                        <td><span class="durum-etiket d-{{ $s['durumum'] }}">{{ $etiketler[$s['durumum']] }}</span></td>
                        <td class="h-tutar">{{ $s['benimTeklifimBicim'] }}</td>
                        <td class="h-tutar">{{ $s['guncelFiyatBicim'] }}</td>
                        <td><a class="btn" href="{{ route('ilan.goster', $s['id']) }}">İncele</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
