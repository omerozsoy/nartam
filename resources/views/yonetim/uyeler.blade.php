@extends('layouts.app')

@section('baslik', 'Üyeler')

@section('content')
    <main class="yonetim">
        <h1>Üyeler</h1>
        @include('yonetim._nav')

        <section class="kart">
            <p class="alt-not">Toplam {{ $uyeler->count() }} üye.</p>
            <table class="tablo">
                <thead>
                <tr><th>#</th><th>Ad Soyad</th><th>E-posta</th><th>Telefon</th><th>Rol</th><th>Teklif</th><th>Kayıt</th></tr>
                </thead>
                <tbody>
                @foreach ($uyeler as $uye)
                    <tr>
                        <td>{{ $uye->id }}</td>
                        <td>{{ $uye->name }}</td>
                        <td>{{ $uye->email }}</td>
                        <td>{{ $uye->telefon ?? '—' }}</td>
                        <td>
                            @if ($uye->rol === 'yonetici')
                                <span class="rozet rozet-acik_artirma">Yönetici</span>
                            @else
                                Üye
                            @endif
                        </td>
                        <td>{{ $uye->teklifler_count }}</td>
                        <td>{{ $uye->created_at?->format('d.m.Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    </main>
@endsection
