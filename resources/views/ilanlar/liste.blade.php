@extends('layouts.app')

@section('baslik', 'Müzayede')

@section('content')
    <div class="kap">
        <main id="lotlar">
            @php($bolumler = ['acik_artirma' => 'Açık Artırma', 'dusuyor' => 'Açık Eksiltme', 'kapandi' => 'Kapandı'])
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
        </main>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/sayac.js') }}?v={{ filemtime(public_path('assets/sayac.js')) }}"></script>
@endpush
