@extends('layouts.app')

@section('baslik', 'Müzayede')

@section('content')
    <div class="kap">
        @if (!empty($muzayede))
            <div class="muzayede-baslik">
                <span class="muzayede-no">{{ $muzayede->no }}. Müzayede</span>
                <h1>{{ $muzayede->ad }}</h1>
                <div class="muzayede-tarih">Başlangıç: {{ $muzayede->baslangic->format('d.m.Y H:i') }}</div>
            </div>
        @endif

        @if ($vitrinGoster ?? true)
            @include('ilanlar._vitrin', ['vitrin' => ($gruplar['acik_artirma'] ?? collect())->take(10)])
        @endif

        <main id="lotlar">
            @php($bolumler = ['acik_artirma' => 'Açık Artırma', 'dusuyor' => 'Fiyatı Düşenler', 'yakinda' => 'Yakında', 'kapandi' => 'Kapandı'])
            @foreach ($bolumler as $durum => $bolumBaslik)
                @php($grup = $gruplar[$durum] ?? collect())
                @if ($grup->isNotEmpty())
                    <section class="lot-bolum">
                        <h2 class="bolum-baslik">
                            {{ $bolumBaslik }}
                            <span>{{ $grup->count() }} lot</span>
                        </h2>
                        <div class="lot-izgara">
                            @foreach ($grup as $ilan)
                                @include('ilanlar._kart', ['ilan' => $ilan])
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
            <p class="hesap-bos arama-yok" data-alan="arama-yok" hidden>Aramanızla eşleşen eser bulunamadı.</p>
        </main>
    </div>
@endsection

@push('scripts')
    @include('ilanlar._vitrin_script')
    <script src="{{ asset('assets/sayac.js') }}?v={{ filemtime(public_path('assets/sayac.js')) }}"></script>
@endpush
