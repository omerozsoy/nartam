@extends('layouts.app')

@section('baslik', 'Yeni Müzayede')

@section('content')
    @include('ilanlar._hero', ['hero' => $hero])

    <div class="kap">
        <h2 class="anasayfa-secki">Sizin için seçtiklerimiz</h2>
        @if (!empty($muzayede))
            <a class="anasayfa-muzayede-ust" href="{{ route('muzayede.goster', $muzayede) }}">
                <span class="am-baslik">{{ $muzayede->no }}. Müzayede · {{ $muzayede->ad }}</span>
                <span class="am-tarih">{{ $muzayede->baslangic->format('d.m.Y H:i') }}</span>
            </a>
        @endif

        @include('ilanlar._vitrin', ['vitrin' => $vitrin])

        @if (($kartlar ?? collect())->isNotEmpty())
            <h2 class="anasayfa-secki">Öne Çıkan Eserler</h2>
            <div class="urun-kartlar">
                @foreach ($kartlar as $ilan)
                    <a class="urun-kart" href="{{ route('ilan.goster', $ilan['id']) }}">
                        <div class="urun-kart-foto {{ $ilan['gorselUrl'] ? '' : 'bos' }}">
                            @if ($ilan['gorselUrl'])
                                <img src="{{ $ilan['gorselUrl'] }}" alt="{{ $ilan['baslik'] }}" loading="lazy">
                            @else
                                {{ mb_strtoupper(mb_substr($ilan['baslik'], 0, 1)) }}
                            @endif
                        </div>
                        <div class="urun-kart-bilgi">
                            @if ($ilan['lotNo'])<span class="uk-lot">Lot {{ $ilan['lotNo'] }}</span>@endif
                            <h3>{{ $ilan['baslik'] }}</h3>
                            @if ($ilan['altBaslik'])<div class="uk-alt">{{ $ilan['altBaslik'] }}</div>@endif
                            <div class="uk-fiyat">{{ $ilan['guncelFiyatBicim'] }}</div>
                            <span class="uk-buton">İncele</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="hizmet-kartlar">
            <div class="hizmet-kart">
                <div class="hk-govde">
                    <span class="hk-etiket">Değerleme</span>
                    <h3>Ekspertiz</h3>
                    <p>Eserlerinizin değerini alanında uzman ekibimizle öğrenin. Satış öncesi ücretsiz ön değerlendirme ile koleksiyonunuzu güvenle yönetin.</p>
                </div>
                <a class="btn" href="{{ route('ekspertiz') }}">Daha Fazla</a>
            </div>
            <div class="hizmet-kart">
                <div class="hk-govde">
                    <span class="hk-etiket">Katalog</span>
                    <h3>Müzayedeler</h3>
                    <p>Güncel ve geçmiş müzayedelerimizi keşfedin; modern ve çağdaş sanatın seçkin eserlerine teklif verin.</p>
                </div>
                <a class="btn" href="{{ route('muzayedeler') }}">Müzayedeye Git</a>
            </div>
            <div class="hizmet-kart">
                <div class="hk-govde">
                    <span class="hk-etiket">Bize Ulaşın</span>
                    <h3>İletişim</h3>
                    <p>Sorularınız, satış ve alım talepleriniz için ekibimizle iletişime geçin.</p>
                </div>
                <a class="btn" href="{{ route('iletisim') }}">İletişim</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('ilanlar._vitrin_script')
@endpush
