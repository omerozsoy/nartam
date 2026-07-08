@extends('layouts.app')

@section('baslik', 'Yeni Müzayede')

@section('content')
    @include('ilanlar._hero', ['hero' => $hero])

    <div class="kap">
        @include('ilanlar._vitrin', ['vitrin' => $vitrin])

        <div class="anasayfa-eylem">
            @if (!empty($muzayede))
                <div class="anasayfa-muzayede">{{ $muzayede->no }}. Müzayede · {{ $muzayede->ad }}</div>
            @endif
            <a class="btn btn-dolu" href="{{ route('muzayedeler') }}">Müzayedeye Git</a>
        </div>
    </div>
@endsection

@push('scripts')
    @include('ilanlar._vitrin_script')
@endpush
