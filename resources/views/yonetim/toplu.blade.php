@extends('layouts.yonetim')

@section('baslik', 'Toplu Ürün Girişi')

@section('content')
    <main class="yonetim">
        <h1>Toplu Ürün Girişi</h1>

        <section class="kart">
            <h2>Excel ile Yükle</h2>
            <p class="alt-not" style="margin-bottom:1rem">
                Sütunlar: <strong>Eserin Adı · Açıklama · Sanatçı Adı · fiyat · Lot No · Provenance</strong>.
                Sanatçı adı başlık, eserin adı alt başlık, fiyat başlangıç fiyatı olur. Lot No'ya göre görsel
                otomatik bağlanır. Yükledikten sonra <strong>önce önizleme</strong> gösterilir.
            </p>
            <form method="post" action="{{ route('yonetim.toplu.onizle') }}" enctype="multipart/form-data" class="izgara-form">
                @csrf
                <label class="genis">Excel Dosyası (.xlsx, .xls, .csv)
                    <input type="file" name="excel" accept=".xlsx,.xls,.csv" required>
                </label>
                <button type="submit" class="btn btn-dolu">Önizle</button>
            </form>
            <p class="alt-not">Saatlik düşüş ve rezerv, her eser için <strong>Eserler → Düzenle</strong>'den tek tek ayarlanır.</p>
        </section>

        @if ($partiler->isNotEmpty())
            <section class="kart">
                <h2>Son İçe Aktarımlar</h2>
                <table class="tablo">
                    <thead><tr><th>Parti</th><th>Eser Sayısı</th><th>Tarih</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($partiler as $parti)
                        <tr>
                            <td>{{ $parti->ithal_kodu }}</td>
                            <td>{{ $parti->adet }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($parti->tarih)->format('d.m.Y H:i') }}</td>
                            <td>
                                <form method="post" action="{{ route('yonetim.toplu.gerial') }}"
                                      onsubmit="return confirm('{{ $parti->adet }} eser silinecek (geri alınamaz). Emin misiniz?')">
                                    @csrf
                                    <input type="hidden" name="kod" value="{{ $parti->ithal_kodu }}">
                                    <button type="submit" class="baglanti-buton" style="color:var(--kritik)">Geri Al</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        <section class="kart">
            <h2>Ya da Elle Yapıştır</h2>
            <p class="alt-not" style="margin-bottom:1rem">
                Her satır bir eser: <code>Başlık | Alt Başlık | Fiyat | Açıklama</code>
            </p>
            <form method="post" action="{{ route('yonetim.toplu.kaydet') }}" class="izgara-form">
                @csrf
                <label class="genis">Satırlar
                    <textarea name="satirlar" rows="6" placeholder="Nejad Melih Devrim (1923-1995) | Soyut | 150000 | Kağıt üzerine guaj, imzalı.">{{ old('satirlar') }}</textarea>
                </label>
                <button type="submit" class="btn btn-dolu">Ekle</button>
            </form>
        </section>
    </main>
@endsection
