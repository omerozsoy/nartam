@extends('layouts.app')

@section('baslik', 'Hesabım')

@php
    $etiketler = ['onde' => 'Önde', 'geride' => 'Geçildiniz', 'kazandi' => 'Kazandınız', 'kaybetti' => 'Kaybettiniz'];
@endphp

@section('content')
    <div class="kap hesap-duzen" id="hesap-panel">
        @include('hesap._kenar')

        <div class="hesap-icerik">
            {{-- Pey Verdiklerim --}}
            <section data-panel="pey">
                <h2 class="hesap-baslik">Pey Verdiğim Eserler</h2>
                @if ($diger->isEmpty())
                    <p class="hesap-bos">Henüz devam eden peyiniz yok. <a href="{{ route('ilanlar.liste') }}">Eserlere göz atın →</a></p>
                @else
                    @foreach ($diger->groupBy('muzayedeId') as $grup)
                        <h3 class="hesap-grup-baslik">{{ $grup->first()['muzayedeBaslik'] }}</h3>
                        <table class="hesap-tablo">
                            <thead>
                            <tr><th></th><th>Eser</th><th>Durum</th><th>Benim Teklifim</th><th>Güncel Fiyat</th><th></th></tr>
                            </thead>
                            <tbody>
                            @foreach ($grup as $s)
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
                    @endforeach
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
            <section data-panel="kazandi" hidden>
                <h2 class="hesap-baslik">Kazandıklarım</h2>
                @if ($kazandiklarim->isEmpty())
                    <p class="hesap-bos">Henüz kazandığınız eser yok.</p>
                @else
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
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/panel.js') }}?v={{ filemtime(public_path('assets/panel.js')) }}"></script>
    <script>
        (function () {
            const menu = document.querySelectorAll('.hesap-menu-oge[data-tab]');
            const paneller = document.querySelectorAll('.hesap-icerik [data-panel]');
            function goster(tab) {
                let bulundu = false;
                paneller.forEach(function (p) { const m = p.dataset.panel === tab; p.hidden = !m; if (m) bulundu = true; });
                if (!bulundu) { tab = 'pey'; paneller.forEach(function (p) { p.hidden = p.dataset.panel !== 'pey'; }); }
                menu.forEach(function (b) { b.classList.toggle('aktif', b.dataset.tab === tab); });
            }
            menu.forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    goster(btn.dataset.tab);
                    history.replaceState(null, '', btn.getAttribute('href'));
                });
            });
            goster(new URLSearchParams(location.search).get('tab') || 'pey');
        })();
    </script>
@endpush
