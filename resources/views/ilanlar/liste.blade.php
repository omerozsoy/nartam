@extends('layouts.app')

@section('baslik', 'Müzayede')

@section('content')
    <div class="kap">
        <div class="satis-basligi">
            <div>
                <div class="satis-tarih">Çevrimiçi Müzayede · nartam</div>
                <h1>nartam Sanat &amp; Antika Müzayedesi</h1>
                <div class="yer">İki fazlı · Fiyat düşer, ilk teklifle açık artırma başlar</div>
            </div>
            <div class="satis-eylem">
                <div class="kapanis">{{ $ilanlar->count() }} lot · canlı</div>
                <div class="butonlar">
                    @guest
                        <a class="btn btn-dolu" href="{{ route('kayit') }}">Kayıt Ol</a>
                        <a class="btn" href="{{ route('giris') }}">Giriş</a>
                    @else
                        <a class="btn" href="#lotlar">Lotları Gör</a>
                    @endguest
                </div>
            </div>
        </div>

        <div class="sekmeler">
            <a href="#lotlar" class="aktif">Lotlar ({{ $ilanlar->count() }})</a>
            <a href="#lotlar">Genel Bakış</a>
        </div>

        <div class="filtre-pills">
            <span class="pill"><span class="nokta"></span> Tümü</span>
            <span class="pill"><span class="nokta"></span> Açık Artırma</span>
            <span class="pill"><span class="nokta"></span> Düşen Fiyat</span>
            <span class="pill"><span class="nokta"></span> Antika</span>
            <span class="pill"><span class="nokta"></span> Sanat</span>
        </div>

        <main class="lot-izgara" id="lotlar">
            @foreach ($ilanlar as $ilan)
                @include('ilanlar._kart', ['ilan' => $ilan])
            @endforeach
        </main>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/sayac.js') }}"></script>
@endpush
