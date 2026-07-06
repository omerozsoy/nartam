@extends('layouts.yonetim')

@section('baslik', 'Toplu Ürün Girişi')

@section('content')
    <main class="yonetim">
        <h1>Toplu Ürün Girişi</h1>

        <section class="kart">
            <h2>Excel ile Yükle</h2>
            <p class="alt-not" style="margin-bottom:1rem">
                Sütunlar: <strong>Eserin Adı · Açıklama · Sanatçı Adı · fiyat · Lot No · Provenance</strong>.
                Sanatçı adı başlık, eserin adı alt başlık, açıklama+provenance açıklama, fiyat başlangıç fiyatı olur.
                Her eser "düşen fiyat" fazında başlar.
            </p>
            <form method="post" action="{{ route('yonetim.toplu.kaydet') }}" enctype="multipart/form-data" class="izgara-form">
                @csrf
                <label class="genis">Excel Dosyası (.xlsx, .xls, .csv)
                    <input type="file" name="excel" accept=".xlsx,.xls,.csv" required>
                </label>
                <button type="submit" class="btn btn-dolu">Excel'i Yükle ve Ekle</button>
            </form>
            <p class="alt-not">Saatlik düşüş ve rezerv (taban) fiyatı, her eser için <strong>Eserler → Düzenle</strong> sayfasından tek tek ayarlanır.</p>
        </section>

        <section class="kart">
            <h2>Ya da Elle Yapıştır</h2>
            <p class="alt-not" style="margin-bottom:1rem">
                Her satır bir eser. Sırayla, dikey çizgi (|) ile ayır:
                <code>Başlık | Alt Başlık | Fiyat | Açıklama</code>
            </p>
            <form method="post" action="{{ route('yonetim.toplu.kaydet') }}" class="izgara-form">
                @csrf
                <label class="genis">Satırlar
                    <textarea name="satirlar" rows="8" placeholder="Nejad Melih Devrim (1923-1995) | Soyut | 150000 | Kağıt üzerine guaj, imzalı.
Ömer Uluç (1931-2010) | Nü | 80000 | Kağıt üzerine karışık teknik.">{{ old('satirlar') }}</textarea>
                </label>
                <button type="submit" class="btn btn-dolu">Ekle</button>
            </form>
        </section>
    </main>
@endsection
