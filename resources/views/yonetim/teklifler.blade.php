@extends('layouts.yonetim')

@section('baslik', 'Teklifler')

@section('content')
    <main class="yonetim">
        <h1>Teklifler @if ($ilan)<small style="font-size:1rem;color:var(--soluk)">· {{ $ilan->baslik }}</small>@endif</h1>

        <section class="kart">
            <p class="alt-not">
                {{ $teklifler->count() }} teklif.
                @if ($ilan) <a href="{{ route('yonetim.teklifler') }}">Tümünü göster</a> @endif
                <span style="float:right">Yönetici görünümü: gerçek isimler</span>
            </p>
            <table class="tablo">
                <thead>
                <tr><th>Tarih</th><th>Üye</th><th>E-posta</th><th>Telefon</th><th>Eser</th><th>Lot</th><th>Tutar</th></tr>
                </thead>
                <tbody>
                @forelse ($teklifler as $teklif)
                    <tr>
                        <td>{{ $teklif->zaman->format('d.m.Y H:i') }}</td>
                        <td>{{ $teklif->kullanici->name ?? '—' }}</td>
                        <td>{{ $teklif->kullanici->email ?? '—' }}</td>
                        <td>{{ $teklif->kullanici->telefon ?? '—' }}</td>
                        <td>{{ $teklif->ilan->baslik ?? '—' }}</td>
                        <td>{{ $teklif->ilan->lot_no ?? '—' }}</td>
                        <td>{{ number_format($teklif->miktar, 0, ',', '.') }} ₺</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="alt-not">Henüz teklif yok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>
    </main>
@endsection
