@extends('layouts.app')

@section('baslik', $baslik)

@section('content')
    <div class="kap">
        <div class="satis-basligi">
            <div>
                <div class="satis-tarih">Yeni Müzayede · Çevrimiçi</div>
                <h1>{{ $baslik }}</h1>
                <div class="yer">{{ $aciklama }}</div>
            </div>
            <div class="satis-eylem">
                <div class="kapanis">{{ $ilanlar->count() }} lot · canlı</div>
            </div>
        </div>

        <main class="lot-izgara" id="lotlar">
            @forelse ($ilanlar as $ilan)
                @include('ilanlar._kart', ['ilan' => $ilan])
            @empty
                <p class="hesap-bos">Şu an bu kategoride ürün yok.</p>
            @endforelse
        </main>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/sayac.js') }}?v={{ filemtime(public_path('assets/sayac.js')) }}"></script>
@endpush
