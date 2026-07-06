@extends('layouts.app')

@section('baslik', 'Kayıt Ol')

@section('content')
    <div class="giris-sayfa">
        <div class="giris-kart">
            <div class="giris-logo">Yeni Müzayede</div>
            <h1>Kayıt Ol</h1>
            <p class="giris-alt">Hesap oluşturup teklif vermeye başlayın.</p>

            <form method="post" action="{{ route('kayit') }}">
                @csrf
                <label>Ad Soyad
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus>
                </label>
                <label>E-posta
                    <input type="email" name="email" value="{{ old('email') }}" required>
                </label>
                <label>Cep Telefonu
                    <input type="tel" name="telefon" value="{{ old('telefon') }}" placeholder="05xx xxx xx xx" required>
                </label>
                <label>Şifre <small>(en az 6 karakter)</small>
                    <input type="password" name="password" minlength="6" required>
                </label>
                <label>Şifre (tekrar)
                    <input type="password" name="password_confirmation" minlength="6" required>
                </label>
                <button type="submit" class="btn btn-dolu giris-btn">Kayıt Ol</button>
            </form>

            <p class="giris-dip">Zaten hesabınız var mı? <a href="{{ route('giris') }}">Giriş yapın</a></p>
        </div>
    </div>
@endsection
