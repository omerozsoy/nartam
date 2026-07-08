@extends('layouts.app')

@section('baslik', 'Müzayede')

@section('content')
    <div class="kap">
        @if (!empty($muzayede))
            @php($tumLotlar = collect($gruplar)->flatten(1))
            @php($sanatcilar = $tumLotlar->pluck('baslik')->filter()->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values())
            @php($kategoriler = $tumLotlar->pluck('kategori')->filter()->unique()->sort()->values())
            <div class="muzayede-ust">
                <h1 class="muzayede-ad-baslik">{{ $muzayede->no }}. Müzayede {{ $muzayede->ad }}</h1>
                <div class="muzayede-meta">
                    {{ $muzayede->baslangic->format('d.m.Y H:i') }}
                    &nbsp;{{ $muzayede->aralik1 }}+{{ $muzayede->aralik2 }} dk / {{ $tumLotlar->count() }} adet eser
                </div>
            </div>

            <div class="muzayede-filtre" data-alan="filtre">
                <input type="search" class="filtre-girdi" data-alan="f-kelime" placeholder="Aranacak Kelime" autocomplete="off" spellcheck="false">
                <input type="number" class="filtre-girdi" data-alan="f-lot" placeholder="Lot No İle Arama" min="1">
                <select class="filtre-girdi" data-alan="f-sanatci">
                    <option value="">Sanatçı Arama</option>
                    @foreach ($sanatcilar as $s)<option value="{{ mb_strtolower($s) }}">{{ $s }}</option>@endforeach
                </select>
                <select class="filtre-girdi" data-alan="f-kategori" @if($kategoriler->isEmpty()) disabled @endif>
                    <option value="">Kategori Arama</option>
                    @foreach ($kategoriler as $k)<option value="{{ mb_strtolower($k) }}">{{ $k }}</option>@endforeach
                </select>
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
    <script src="{{ asset('assets/muzayede-filtre.js') }}?v={{ filemtime(public_path('assets/muzayede-filtre.js')) }}"></script>
    <script src="{{ asset('assets/sayac.js') }}?v={{ filemtime(public_path('assets/sayac.js')) }}"></script>
@endpush
