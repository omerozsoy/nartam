@extends('layouts.app')

@section('baslik', 'Üye · ' . $uye->name)

@section('content')
    <main class="yonetim">
        <h1>{{ $uye->name }} @if ($uye->engelli)<span class="rozet rozet-kapandi">Engelli</span>@endif</h1>
        @include('yonetim._nav')

        <section class="kart">
            <table class="tablo">
                <tr><th>E-posta</th><td>{{ $uye->email }}</td></tr>
                <tr><th>Telefon</th><td>{{ $uye->telefon ?? '—' }}</td></tr>
                <tr><th>Rol</th><td>{{ $uye->rol === 'yonetici' ? 'Yönetici' : 'Üye' }}</td></tr>
                <tr><th>Teklif Sayısı</th><td>{{ $uye->teklifler_count }}</td></tr>
                <tr><th>Kayıt</th><td>{{ $uye->created_at?->format('d.m.Y H:i') }}</td></tr>
            </table>
            @unless ($uye->yonetici())
                <form method="post" action="{{ route('yonetim.uye.engelle', $uye) }}" style="margin-top:1rem"
                      onsubmit="return confirm('{{ $uye->engelli ? 'Engeli kaldır?' : 'Üyeyi engelle?' }}')">
                    @csrf
                    <button type="submit" class="btn">{{ $uye->engelli ? 'Engeli Kaldır' : 'Üyeyi Engelle' }}</button>
                </form>
            @endunless
        </section>

        <section class="kart">
            <h2>Adresler</h2>
            @forelse ($adresler as $adres)
                <div class="adres-kart">
                    <span class="rozet">{{ $adres->turEtiket() }}</span>
                    <div class="adres-ad">{{ $adres->ad_soyad }}</div>
                    @if ($adres->telefon)<div class="lot-alt">{{ $adres->telefon }}</div>@endif
                    <p class="adres-metin">{{ $adres->adres }}</p>
                    <div class="lot-alt">{{ trim(($adres->ilce ? $adres->ilce . ' / ' : '') . $adres->il . ' ' . $adres->posta_kodu) }}</div>
                </div>
            @empty
                <p class="alt-not">Adres eklenmemiş.</p>
            @endforelse
        </section>

        <section class="kart">
            <h2>Teklifleri</h2>
            <table class="tablo">
                <thead><tr><th>Tarih</th><th>Eser</th><th>Lot</th><th>Tutar</th></tr></thead>
                <tbody>
                @forelse ($teklifler as $teklif)
                    <tr>
                        <td>{{ $teklif->zaman->format('d.m.Y H:i') }}</td>
                        <td>{{ $teklif->ilan->baslik ?? '—' }}</td>
                        <td>{{ $teklif->ilan->lot_no ?? '—' }}</td>
                        <td>{{ number_format($teklif->miktar, 0, ',', '.') }} ₺</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="alt-not">Teklif yok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>
    </main>
@endsection
