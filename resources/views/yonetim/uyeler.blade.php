@extends('layouts.yonetim')

@section('baslik', 'Üyeler')

@section('content')
    <main class="yonetim">
        <h1>Üyeler</h1>

        <section class="kart">
            <h2 style="margin-top:0">Yeni Üye Ekle</h2>
            <form method="post" action="{{ route('yonetim.uye.ekle') }}" class="izgara-form">
                @csrf
                <label>Ad Soyad
                    <input type="text" name="name" value="{{ old('name') }}" required>
                </label>
                <label>E-posta
                    <input type="email" name="email" value="{{ old('email') }}" required>
                </label>
                <label>Telefon
                    <input type="text" name="telefon" value="{{ old('telefon') }}" placeholder="opsiyonel">
                </label>
                <label>Şifre
                    <input type="text" name="sifre" value="{{ old('sifre') }}" required minlength="6" placeholder="en az 6 karakter">
                </label>
                <label class="onay-satir">
                    <input type="checkbox" name="rol" value="yonetici" @checked(old('rol')==='yonetici')> Yönetici yetkisi
                </label>
                <button type="submit" class="btn btn-dolu">Üye Ekle</button>
            </form>
        </section>

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
                        <td><a href="{{ route('yonetim.uye', $uye) }}">{{ $uye->name }}</a>
                            @if ($uye->engelli)<span class="rozet rozet-kapandi">Engelli</span>@endif
                        </td>
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
