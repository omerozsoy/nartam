@extends('layouts.app')

@section('baslik', 'Kayıt Ol')

@section('content')
    <main class="dar-form">
        <h1>Kayıt Ol</h1>
        <form method="post" action="{{ route('kayit') }}">
            @csrf
            <label>Ad
                <input type="text" name="name" value="{{ old('name') }}" required autofocus>
            </label>
            <label>E-posta
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label>Şifre <small>(en az 6 karakter)</small>
                <input type="password" name="password" minlength="6" required>
            </label>
            <label>Şifre (tekrar)
                <input type="password" name="password_confirmation" minlength="6" required>
            </label>
            <button type="submit">Kayıt Ol</button>
        </form>
        <p class="alt-not">Zaten hesabın var mı? <a href="{{ route('giris') }}">Giriş yap</a></p>
    </main>
@endsection
