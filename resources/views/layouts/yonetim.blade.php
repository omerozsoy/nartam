<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('baslik', 'Yönetim') — Yeni Müzayede</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
</head>
<body>
<div class="yonetim-duzen">
    <aside class="yonetim-kenar">
        <a class="yk-marka" href="{{ route('yonetim') }}">Yeni Müzayede</a>
        <div class="yk-alt">Yönetim</div>
        <nav class="yk-menu">
            <a href="{{ route('yonetim') }}" class="{{ request()->routeIs('yonetim') ? 'aktif' : '' }}">Panel</a>
            <a href="{{ route('yonetim.eserler') }}" class="{{ request()->routeIs('yonetim.eserler') || request()->routeIs('yonetim.ilan.duzenle') ? 'aktif' : '' }}">Eserler</a>
            <a href="{{ route('yonetim.eser.yeni') }}" class="{{ request()->routeIs('yonetim.eser.yeni') ? 'aktif' : '' }}">Yeni Eser Ekle</a>
            <a href="{{ route('yonetim.toplu') }}" class="{{ request()->routeIs('yonetim.toplu') ? 'aktif' : '' }}">Toplu Ürün Girişi</a>
            <a href="{{ route('yonetim.pey') }}" class="{{ request()->routeIs('yonetim.pey') ? 'aktif' : '' }}">Pey Adımları</a>
            <a href="{{ route('yonetim.uyeler') }}" class="{{ request()->routeIs('yonetim.uyeler') || request()->routeIs('yonetim.uye') ? 'aktif' : '' }}">Üyeler</a>
        </nav>
        <div class="yk-dip">
            <a href="{{ route('ilanlar.liste') }}">← Siteye dön</a>
            <form method="post" action="{{ route('cikis') }}">
                @csrf
                <button type="submit" class="baglanti-buton">Çıkış</button>
            </form>
        </div>
    </aside>

    <div class="yonetim-icerik">
        @if (session('basari'))<div class="flash flash-basari">{{ session('basari') }}</div>@endif
        @if ($errors->any())<div class="flash flash-hata">{{ $errors->first() }}</div>@endif
        @yield('content')
    </div>
</div>
</body>
</html>
