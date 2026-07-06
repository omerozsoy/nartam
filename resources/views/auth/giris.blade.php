@extends('layouts.app')

@section('baslik', 'Giriş Yap')

@section('content')
    <main class="dar-form">
        <h1>Giriş Yap</h1>
        <form method="post" action="{{ route('giris') }}">
            @csrf
            <label>E-posta
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>
            <label>Şifre
                <input type="password" name="password" required>
            </label>
            <button type="submit">Giriş Yap</button>
        </form>
        <p class="alt-not">Hesabın yok mu? <a href="{{ route('kayit') }}">Kayıt ol</a></p>
        <p class="alt-not ipucu">Demo: <code>admin@nartam.test</code> / <code>admin123</code></p>
    </main>
@endsection
