@extends('layouts.yonetim')

@section('baslik', 'İletişim Bilgileri')

@section('content')
    <main class="yonetim">
        <h1>İletişim Bilgileri</h1>

        <section class="kart">
            <form method="post" action="{{ route('yonetim.iletisim.guncelle') }}" class="dikey-form">
                @csrf
                <label>Adres
                    <textarea name="iletisim_adres" rows="3">{{ old('iletisim_adres', $a['iletisim_adres']) }}</textarea>
                </label>
                <div class="izgara-form">
                    <label>Telefon
                        <input type="text" name="iletisim_telefon" value="{{ old('iletisim_telefon', $a['iletisim_telefon']) }}">
                    </label>
                    <label>E-posta
                        <input type="text" name="iletisim_eposta" value="{{ old('iletisim_eposta', $a['iletisim_eposta']) }}">
                    </label>
                    <label>Çalışma Saatleri
                        <input type="text" name="iletisim_saatler" value="{{ old('iletisim_saatler', $a['iletisim_saatler']) }}" placeholder="Hafta içi 09:00–18:00">
                    </label>
                </div>

                <h2>Sosyal Medya</h2>
                <div class="izgara-form">
                    <label>Instagram
                        <input type="text" name="sosyal_instagram" value="{{ old('sosyal_instagram', $a['sosyal_instagram']) }}" placeholder="https://instagram.com/...">
                    </label>
                    <label>Facebook
                        <input type="text" name="sosyal_facebook" value="{{ old('sosyal_facebook', $a['sosyal_facebook']) }}" placeholder="https://facebook.com/...">
                    </label>
                    <label>X (Twitter)
                        <input type="text" name="sosyal_twitter" value="{{ old('sosyal_twitter', $a['sosyal_twitter']) }}" placeholder="https://x.com/...">
                    </label>
                    <label>WhatsApp
                        <input type="text" name="sosyal_whatsapp" value="{{ old('sosyal_whatsapp', $a['sosyal_whatsapp']) }}" placeholder="https://wa.me/90...">
                    </label>
                </div>

                <label>Serbest Metin (açıklama)
                    <textarea name="iletisim_metin" rows="4">{{ old('iletisim_metin', $a['iletisim_metin']) }}</textarea>
                </label>

                <label>Harita (Google Maps gömme kodu / iframe)
                    <textarea name="iletisim_harita" rows="4" placeholder="Google Maps > Paylaş > Harita yerleştir > iframe kodunu yapıştırın">{{ old('iletisim_harita', $a['iletisim_harita']) }}</textarea>
                </label>

                <button type="submit" class="btn btn-dolu">Kaydet</button>
            </form>
        </section>
    </main>
@endsection
