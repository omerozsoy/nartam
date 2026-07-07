@extends('layouts.app')

@section('baslik', $baslik)

@section('content')
    <div class="kap">
        <div class="sayfa">
            <h1>{{ $baslik }}</h1>
            @if ($icerik)
                <div class="sayfa-metin">{!! nl2br(e($icerik)) !!}</div>
            @else
                <p class="hesap-bos">İçerik yakında eklenecek.</p>
            @endif
        </div>
    </div>
@endsection
