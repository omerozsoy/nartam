@php($aktifTab = request()->routeIs('hesabim') ? request('tab', 'pey') : null)
<aside class="hesap-kenar">
    <h1 class="hesap-ad">Hesabım</h1>
    <nav class="hesap-menu">
        <a class="hesap-menu-oge {{ $aktifTab === 'pey' ? 'aktif' : '' }}" href="{{ route('hesabim') }}" data-tab="pey">Pey Verdiklerim</a>
        <a class="hesap-menu-oge {{ $aktifTab === 'takip' ? 'aktif' : '' }}" href="{{ route('hesabim', ['tab' => 'takip']) }}" data-tab="takip">Takip Ettiklerim</a>
        <a class="hesap-menu-oge {{ $aktifTab === 'kazandi' ? 'aktif' : '' }}" href="{{ route('hesabim', ['tab' => 'kazandi']) }}" data-tab="kazandi">Kazandıklarım</a>
        <a class="hesap-menu-oge {{ request()->routeIs('adresler') ? 'aktif' : '' }}" href="{{ route('adresler') }}">Adreslerim</a>
        <a class="hesap-menu-oge {{ request()->routeIs('bilgiler') ? 'aktif' : '' }}" href="{{ route('bilgiler') }}">Kişisel Bilgilerim</a>
        <form method="post" action="{{ route('cikis') }}">
            @csrf
            <button type="submit" class="hesap-menu-oge hesap-cikis">Çıkış</button>
        </form>
    </nav>
</aside>
