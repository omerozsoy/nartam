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
    </div>
@endsection

@push('scripts')
    @include('ilanlar._vitrin_script')
@endpush
