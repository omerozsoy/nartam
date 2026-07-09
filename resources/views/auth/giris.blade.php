@extends('layouts.app')

@section('baslik', 'Giriş Yap')

@section('content')
    <div class="giris-sayfa">
        <div class="giris-kart">
            <div class="giris-logo">Yeni Müzayede</div>
            <h1>Giriş Yap</h1>
            <p class="giris-alt">Teklif verebilmek için hesabınıza giriş yapın.</p>

            <form method="post" action="{{ route('giris') }}">
                @csrf
                <label>E-posta
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus>
                </label>
                <label>Şifre
                    <input type="password" name="password" required>
                </label>
                <label class="beni-hatirla">
                    <input type="checkbox" name="beni_hatirla" value="1" {{ old('beni_hatirla', true) ? 'checked' : '' }}>
                    <span>Beni hatırla (bir sonraki girişte şifre sorulmasın)</span>
                </label>
                <button type="submit" class="btn btn-dolu giris-btn">Giriş Yap</button>
            </form>

            <p class="giris-dip">Hesabınız yok mu? <a href="{{ route('kayit') }}">Kayıt olun</a></p>
        </div>
    </div>
@endsection
