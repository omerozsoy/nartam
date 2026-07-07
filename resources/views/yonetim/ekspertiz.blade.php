@extends('layouts.yonetim')

@section('baslik', 'Ekspertiz Sayfası')

@section('content')
    <main class="yonetim">
        <h1>Ekspertiz Sayfası</h1>

        <section class="kart">
            <form method="post" action="{{ route('yonetim.ekspertiz.guncelle') }}" class="dikey-form">
                @csrf
                <label>Başlık
                    <input type="text" name="ekspertiz_baslik" value="{{ old('ekspertiz_baslik', $baslik) }}" required>
                </label>
                <label>İçerik
                    <textarea name="ekspertiz_metin" rows="14" placeholder="Ekspertiz hizmetinizi anlatan metni buraya yazın…">{{ old('ekspertiz_metin', $icerik) }}</textarea>
                </label>
                <button type="submit" class="btn btn-dolu">Kaydet</button>
            </form>
        </section>
    </main>
@endsection
