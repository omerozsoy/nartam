<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('baslik', 'nartam Müzayede') — nartam</title>
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
</head>
<body>
<nav class="ust-bar">
    <a class="marka" href="{{ route('ilanlar.liste') }}">nartam</a>
    <div class="ust-baglantilar">
        @auth
            @if (auth()->user()->yonetici())
                <a href="{{ route('yonetim') }}">Yönetim</a>
            @endif
            <span class="kullanici-ad">{{ auth()->user()->name }}</span>
            <form method="post" action="{{ route('cikis') }}" class="satir-ici">
                @csrf
                <button type="submit" class="baglanti-buton">Çıkış</button>
            </form>
        @else
            <a href="{{ route('giris') }}">Giriş</a>
            <a href="{{ route('kayit') }}" class="vurgu-baglanti">Kayıt Ol</a>
        @endauth
    </div>
</nav>

@if (session('basari'))
    <div class="flash flash-basari">{{ session('basari') }}</div>
@endif
@if ($errors->any())
    <div class="flash flash-hata">{{ $errors->first() }}</div>
@endif

@yield('content')

@stack('scripts')
</body>
</html>
