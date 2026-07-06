<div class="sekmeler" style="margin-bottom:1.5rem">
    <a href="{{ route('hesabim') }}" class="{{ request()->routeIs('hesabim') ? 'aktif' : '' }}">Pey Verdiklerim</a>
    <a href="{{ route('adresler') }}" class="{{ request()->routeIs('adresler') ? 'aktif' : '' }}">Adreslerim</a>
</div>
