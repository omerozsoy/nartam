<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <script>window.PEY_ADIMLARI = @json(\App\Models\Ilan::peyKademeleri());</script>
    <title>@yield('baslik', 'Yeni Müzayede') — Yeni Müzayede</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}?v={{ filemtime(public_path('assets/style.css')) }}">
</head>
<body>
<div class="ust-serit">
    <div class="ust-serit-ic">
        @auth
            <a href="{{ route('hesabim') }}">Hesabım</a>
            @if (auth()->user()->yonetici())
                <a href="{{ route('yonetim') }}">Yönetim</a>
            @endif
            <span class="ust-serit-ad">{{ auth()->user()->name }}</span>
            <form method="post" action="{{ route('cikis') }}">
                @csrf
                <button type="submit" class="baglanti-buton">Çıkış</button>
            </form>
        @else
            <a href="{{ route('giris') }}">Giriş</a>
            <a href="{{ route('kayit') }}">Kayıt Ol</a>
        @endauth
    </div>
</div>
<header class="ust-bar">
    <div class="marka-kutu">
        <a class="marka" href="{{ route('ilanlar.liste') }}">Yeni Müzayede</a>
    </div>
    <input type="checkbox" id="mobil-menu" class="mobil-anahtar" hidden>
    <label for="mobil-menu" class="hamburger" aria-label="Menü"><span></span><span></span><span></span></label>
    <nav class="ust-nav">
        <a href="{{ route('ilanlar.liste') }}" class="{{ request()->routeIs('ilanlar.liste') ? 'aktif' : '' }}">Ana Sayfa</a>
        <a href="{{ route('muzayedeler') }}" class="{{ request()->routeIs('muzayedeler') || request()->routeIs('muzayede.goster') ? 'aktif' : '' }}">Müzayedeler</a>
        <a href="{{ route('ekspertiz') }}" class="{{ request()->routeIs('ekspertiz') ? 'aktif' : '' }}">Ekspertiz</a>
        <a href="{{ route('iletisim') }}" class="{{ request()->routeIs('iletisim') ? 'aktif' : '' }}">İletişim</a>
    </nav>
    <div class="arama arama-ust" data-alan="arama">
        <input type="search" class="arama-girdi" data-alan="arama-girdi" placeholder="Ara…" autocomplete="off" spellcheck="false" aria-label="Ara">
        <ul class="arama-oneri" data-alan="arama-oneri" hidden></ul>
    </div>
</header>

<div class="ticker-wrap" data-alan="ticker" hidden>
    <span class="ticker-etiket">Düşen Fiyatlar</span>
    <div class="ticker-govde"><div class="ticker" data-alan="ticker-ray"></div></div>
</div>

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

@auth
    <div class="modal-arka" data-alan="teklif-modal" hidden>
        <div class="modal-kutu">
            <div class="modal-baslik">Teklifinizi Onaylayın</div>
            <p class="modal-alt" data-alan="modal-alt">Teklifiniz</p>
            <div class="modal-tutar" data-alan="modal-tutar">—</div>
            <p class="modal-not" data-alan="modal-not" hidden>Bu tutar sizin <strong>gizli maksimumunuzdur</strong>; başkaları teklif verdikçe sistem, bu tutara kadar sizin adınıza otomatik pey verir.</p>
            <div class="modal-butonlar">
                <button type="button" class="btn" data-alan="modal-vazgec">Vazgeç</button>
                <button type="button" class="btn btn-dolu" data-alan="modal-onayla">Onayla ve Teklif Ver</button>
            </div>
        </div>
    </div>
@endauth

<button type="button" class="yukari-don" data-alan="yukari-don" aria-label="En üste dön" hidden>
    <span aria-hidden="true">↑</span>
</button>
<script>
    (function () {
        var btn = document.querySelector('[data-alan="yukari-don"]');
        if (!btn) { return; }
        function guncelle() { btn.hidden = window.scrollY < 400; }
        window.addEventListener('scroll', guncelle, { passive: true });
        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        guncelle();
    })();
</script>
<script src="{{ asset('assets/ticker.js') }}?v={{ filemtime(public_path('assets/ticker.js')) }}"></script>
<script src="{{ asset('assets/arama.js') }}?v={{ filemtime(public_path('assets/arama.js')) }}"></script>
<script src="{{ asset('assets/lightbox.js') }}?v={{ filemtime(public_path('assets/lightbox.js')) }}"></script>
@stack('scripts')
</body>
</html>
