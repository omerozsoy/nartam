@extends('layouts.app')

@section('baslik', 'Ödeme')

@section('content')
    <div class="kap dar-form">
        <a class="geri-bag" href="{{ route('hesabim') }}">‹ Hesabım</a>
        <h1>Ödeme</h1>

        <div class="detay-kutu">
            <div class="etiket">Kazanılan Lot</div>
            <div class="h-ad" style="margin:.2rem 0 1rem">{{ $ilan->baslik }}</div>

            <div class="etiket">Ödenecek Tutar</div>
            <div class="fiyat" style="font-size:1.8rem">{{ number_format((int) $ilan->guncel_teklif, 0, ',', '.') }} ₺</div>
        </div>

        <p class="alt-not">
            Ödeme altyapısı (kredi kartı / havale) yakında eklenecek. Şimdilik ödeme ve teslimat
            için sizinle iletişime geçilecektir.
        </p>
        <button class="btn btn-dolu" disabled>Ödeme Yap (yakında)</button>
    </div>
@endsection
