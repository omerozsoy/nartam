@extends('layouts.app')

@section('baslik', $baslik)

@section('content')
    <div class="kap">
        @include('ilanlar._vitrin', ['vitrin' => $ilanlar->where('durum', 'acik_artirma')->take(10)])

        <div class="satis-basligi">
            <div>
                <h1>{{ $baslik }}</h1>
                <div class="yer">{{ $aciklama }}</div>
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
    @include('ilanlar._vitrin_script')
    <script src="{{ asset('assets/sayac.js') }}?v={{ filemtime(public_path('assets/sayac.js')) }}"></script>
@endpush
