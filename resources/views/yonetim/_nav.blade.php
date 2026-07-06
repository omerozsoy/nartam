<div class="sekmeler" style="margin-bottom:1.5rem">
    <a href="{{ route('yonetim') }}" class="{{ request()->routeIs('yonetim') ? 'aktif' : '' }}">İlanlar</a>
    <a href="{{ route('yonetim.uyeler') }}" class="{{ request()->routeIs('yonetim.uyeler') ? 'aktif' : '' }}">Üyeler</a>
    <a href="{{ route('yonetim.teklifler') }}" class="{{ request()->routeIs('yonetim.teklifler') ? 'aktif' : '' }}">Teklifler</a>
</div>
