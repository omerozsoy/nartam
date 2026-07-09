@extends('layouts.app')

@section('baslik', 'Kişisel Bilgilerim')

@section('content')
    <div class="kap hesap-duzen">
        @include('hesap._kenar')

        <div class="hesap-icerik">
            <h2 class="hesap-baslik">Kişisel Bilgilerim</h2>

            <div class="adres-izgara">
                <section class="kart">
                    <h2>Hesap Bilgileri</h2>
                    <form method="post" action="{{ route('bilgiler.guncelle') }}" class="izgara-form">
                        @csrf
                        <label class="genis">Ad Soyad
                            <input type="text" name="name" value="{{ old('name', $kullanici->name) }}" required>
                        </label>
                        <label>E-posta
                            <input type="email" name="email" value="{{ old('email', $kullanici->email) }}" required>
                        </label>
                        <label>Telefon
                            <input type="tel" name="telefon" value="{{ old('telefon', $kullanici->telefon) }}">
                        </label>
                        <button type="submit" class="btn btn-dolu genis">Bilgileri Kaydet</button>
                    </form>
                </section>

                <section class="kart">
                    <h2>Şifre Değiştir</h2>
                    <p class="lot-alt" style="margin-bottom:1rem">Şifrenizi değiştirmek istemiyorsanız boş bırakın.</p>
                    <form method="post" action="{{ route('bilgiler.guncelle') }}" class="izgara-form">
                        @csrf
                        <input type="hidden" name="name" value="{{ $kullanici->name }}">
                        <input type="hidden" name="email" value="{{ $kullanici->email }}">
                        <input type="hidden" name="telefon" value="{{ $kullanici->telefon }}">
                        <label class="genis">Yeni Şifre
                            <input type="password" name="password" autocomplete="new-password" placeholder="En az 8 karakter">
                        </label>
                        <label class="genis">Yeni Şifre (Tekrar)
                            <input type="password" name="password_confirmation" autocomplete="new-password">
                        </label>
                        <button type="submit" class="btn btn-dolu genis">Şifreyi Güncelle</button>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection
