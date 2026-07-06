@extends('layouts.yonetim')

@section('baslik', 'Panel')

@section('content')
    <main class="yonetim">
        <h1>Panel</h1>
        <p class="alt-not" style="margin-bottom:1.5rem">Yeni Müzayede yönetim paneline hoş geldiniz.</p>

        <div class="istatistik">
            <div class="ist-kart"><span class="ist-sayi">{{ $istatistik['uye'] }}</span><span class="etiket">Üye</span></div>
            <div class="ist-kart"><span class="ist-sayi">{{ $istatistik['ilan'] }}</span><span class="etiket">Eser</span></div>
            <div class="ist-kart"><span class="ist-sayi">{{ $istatistik['acikArtirma'] }}</span><span class="etiket">Açık Artırma</span></div>
            <div class="ist-kart"><span class="ist-sayi">{{ $istatistik['dusuyor'] }}</span><span class="etiket">Düşen Fiyat</span></div>
            <div class="ist-kart"><span class="ist-sayi">{{ $istatistik['teklif'] }}</span><span class="etiket">Teklif</span></div>
            <div class="ist-kart"><span class="ist-sayi">{{ $istatistik['toplamDeger'] }}</span><span class="etiket">Toplam Değer</span></div>
        </div>
    </main>
@endsection
