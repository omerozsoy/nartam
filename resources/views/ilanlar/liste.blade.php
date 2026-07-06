@extends('layouts.app')

@section('baslik', 'nartam Müzayede')

@section('content')
    <header class="sayfa-baslik">
        <h1>nartam Müzayede</h1>
        <p>Teklif gelene kadar fiyat düşer; ilk teklifle açık artırma başlar.</p>
    </header>

    <main class="kalemler" id="ilanlar">
        @foreach ($ilanlar as $ilan)
            <article
                class="kalem durum-{{ $ilan['durum'] }}"
                data-id="{{ $ilan['id'] }}"
                data-durum="{{ $ilan['durum'] }}"
                @if ($ilan['bitisTs']) data-bitis="{{ $ilan['bitisTs'] }}" @endif
                @if ($ilan['sonrakiDususTs']) data-sonraki-dusus="{{ $ilan['sonrakiDususTs'] }}" @endif
                data-min="{{ $ilan['minTeklif'] }}"
            >
                <span class="rozet">{{ $ilan['durumEtiket'] }}</span>
                <h2>{{ $ilan['baslik'] }}</h2>

                <p class="fiyat" data-alan="fiyat" data-deger="{{ $ilan['guncelFiyat'] }}">{{ $ilan['guncelFiyatBicim'] }}</p>

                <p class="sayac-etiket" data-alan="sayac-etiket">
                    @if ($ilan['durum'] === 'dusuyor')
                        Sonraki düşüşe
                    @elseif ($ilan['durum'] === 'acik_artirma')
                        Bitişe kalan
                    @else
                        Kazanan: {{ $ilan['sonTeklifSahibi'] ?? '—' }}
                    @endif
                </p>
                @if ($ilan['durum'] !== 'kapandi')
                    <p class="sayac" role="timer" data-alan="sayac">--:--</p>
                @endif

                @if ($ilan['durum'] !== 'kapandi')
                    @auth
                        <form class="teklif-form" data-alan="teklif-form">
                            @csrf
                            <input type="hidden" name="ilan_id" value="{{ $ilan['id'] }}">
                            <input
                                type="number" name="miktar" step="1"
                                min="{{ $ilan['minTeklif'] }}"
                                value="{{ $ilan['minTeklif'] }}"
                                data-alan="miktar" required
                            >
                            <button type="submit">
                                {{ $ilan['durum'] === 'dusuyor' ? 'Bu Fiyata Al' : 'Teklif Ver' }}
                            </button>
                            <span class="teklif-mesaj" data-alan="teklif-mesaj"></span>
                        </form>
                    @else
                        <a class="baglanti-buton dolu" href="{{ route('giris') }}">Teklif için giriş yap</a>
                    @endauth
                @endif
            </article>
        @endforeach
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('assets/sayac.js') }}"></script>
@endpush
