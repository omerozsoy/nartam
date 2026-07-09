@extends('layouts.app')

@section('baslik', 'Hesabım')

@php
    $etiketler = ['onde' => 'Önde', 'geride' => 'Geçildiniz', 'kazandi' => 'Kazandınız', 'kaybetti' => 'Kaybettiniz'];
@endphp

@section('content')
    <div class="kap hesap-duzen" id="hesap-panel">
        <aside class="hesap-kenar">
            <h1 class="hesap-ad">Hesabım</h1>
            <nav class="hesap-menu">
                <button type="button" class="hesap-menu-oge aktif" data-tab="pey">Pey Verdiklerim</button>
                <button type="button" class="hesap-menu-oge" data-tab="takip">Takip Ettiklerim</button>
                @if ($kazandiklarim->isNotEmpty())
                    <button type="button" class="hesap-menu-oge" data-tab="kazandi">Kazandıklarım</button>
                @endif
                <a class="hesap-menu-oge" href="{{ route('adresler') }}">Adreslerim</a>
                <form method="post" action="{{ route('cikis') }}">
                    @csrf
                    <button type="submit" class="hesap-menu-oge hesap-cikis">Çıkış</button>
                </form>
            </nav>
        </aside>

        <div class="hesap-icerik">
            {{-- Pey Verdiklerim --}}
            <section data-panel="pey">
                <h2 class="hesap-baslik">Pey Verdiğim Eserler</h2>
                @if ($diger->isEmpty())
                    <p class="hesap-bos">Henüz devam eden peyiniz yok. <a href="{{ route('ilanlar.liste') }}">Eserlere göz atın →</a></p>
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
            </section>

            {{-- Takip Ettiklerim --}}
            <section data-panel="takip" hidden>
                <h2 class="hesap-baslik">Takip Ettiklerim</h2>
                @if ($takipEttiklerim->isEmpty())
                    <p class="hesap-bos">Henüz takip ettiğiniz eser yok. Kartlardaki "Takip Et" ile ekleyebilirsiniz.</p>
                @else
                    <table class="hesap-tablo">
                        <tbody>
                        @foreach ($takipEttiklerim as $s)
                            <tr>
                                <td class="h-gorsel">
                                    @if ($s['gorselUrl'])<img src="{{ $s['gorselUrl'] }}" alt="{{ $s['baslik'] }}">@else<span class="h-bos">{{ mb_strtoupper(mb_substr($s['baslik'], 0, 1)) }}</span>@endif
                                </td>
                                <td>
                                    @if ($s['lotNo'])<div class="lot-no">LOT {{ $s['lotNo'] }}</div>@endif
                                    <div class="h-ad">{{ $s['baslik'] }}</div>
                                    @if ($s['altBaslik'])<div class="lot-alt">{{ $s['altBaslik'] }}</div>@endif
                                </td>
                                <td><span class="durum-etiket">{{ $s['durumEtiket'] }}</span></td>
                                <td class="h-tutar">{{ $s['guncelFiyatBicim'] }}</td>
                                <td style="white-space:nowrap">
                                    <a class="btn" href="{{ route('ilan.goster', $s['id']) }}">İncele</a>
                                    <button type="button" class="btn takip-btn takip-aktif" data-alan="takip" data-id="{{ $s['id'] }}">Takip Ediliyor</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            {{-- Kazandıklarım --}}
            @if ($kazandiklarim->isNotEmpty())
                <section data-panel="kazandi" hidden>
                    <h2 class="hesap-baslik">Kazandıklarım</h2>
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
                </section>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/panel.js') }}?v={{ filemtime(public_path('assets/panel.js')) }}"></script>
    <script>
        (function () {
            const menu = document.querySelectorAll('.hesap-menu-oge[data-tab]');
            const paneller = document.querySelectorAll('.hesap-icerik [data-panel]');
            menu.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const hedef = btn.dataset.tab;
                    menu.forEach(function (b) { b.classList.toggle('aktif', b === btn); });
                    paneller.forEach(function (p) { p.hidden = p.dataset.panel !== hedef; });
                });
            });
        })();
    </script>
@endpush
