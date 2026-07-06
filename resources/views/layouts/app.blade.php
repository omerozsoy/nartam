<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('baslik', 'Yeni Müzayede') — Yeni Müzayede</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
</head>
<body>
<header class="ust-bar">
    <a class="marka" href="{{ route('ilanlar.liste') }}">Yeni Müzayede</a>
    <nav class="ust-nav">
        <a href="{{ route('ilanlar.liste') }}">Müzayedeler</a>
        <a href="{{ route('ilanlar.liste') }}">Nasıl Çalışır</a>
        <a href="{{ route('ilanlar.liste') }}">Hakkımızda</a>
    </nav>
    <div class="ust-sag">
        @auth
            <a href="{{ route('hesabim') }}">Hesabım</a>
            @if (auth()->user()->yonetici())
                <a href="{{ route('yonetim') }}">Yönetim</a>
            @endif
            <span>{{ auth()->user()->name }}</span>
            <form method="post" action="{{ route('cikis') }}">
                @csrf
                <button type="submit" class="baglanti-buton">Çıkış</button>
            </form>
        @else
            <a href="{{ route('giris') }}">Giriş</a>
            <a href="{{ route('kayit') }}">Kayıt Ol</a>
        @endauth
    </div>
</header>

@if (session('basari'))
    <div class="flash flash-basari">{{ session('basari') }}</div>
@endif
@if ($errors->any())
    <div class="flash flash-hata">{{ $errors->first() }}</div>
@endif

@yield('content')

<footer class="alt-bilgi">
    <div class="sutunlar">
        <div>
            <h4>Yardım</h4>
            <ul>
                <li><a href="#">Nasıl teklif verilir</a></li>
                <li><a href="#">Sıkça sorulanlar</a></li>
                <li><a href="#">İletişim</a></li>
            </ul>
        </div>
        <div>
            <h4>Kurumsal</h4>
            <ul>
                <li><a href="#">Hakkımızda</a></li>
                <li><a href="#">Kariyer</a></li>
                <li><a href="#">Basın</a></li>
            </ul>
        </div>
        <div>
            <h4>Hizmetler</h4>
            <ul>
                <li><a href="#">Değerleme</a></li>
                <li><a href="#">Özel satış</a></li>
                <li><a href="#">Danışmanlık</a></li>
            </ul>
        </div>
        <div>
            <h4>Bilgi</h4>
            <ul>
                <li><a href="#">Şartlar ve koşullar</a></li>
                <li><a href="#">Gizlilik</a></li>
                <li><a href="#">Çerezler</a></li>
            </ul>
        </div>
    </div>
    <div class="alt-telif">© Yeni Müzayede {{ date('Y') }}</div>
</footer>

@stack('scripts')
</body>
</html>
