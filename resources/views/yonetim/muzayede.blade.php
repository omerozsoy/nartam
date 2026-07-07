@extends('layouts.yonetim')

@section('baslik', 'Müzayede Ayarları')

@section('content')
    <main class="yonetim">
        <h1>Müzayede Ayarları — Kademeli Kapanış</h1>

        <section class="kart">
            <p class="alt-not">
                İlk lot belirtilen zamanda kapanır; sonraki lotlar sıra ile aşağıdaki aralıklarla kapanır.
                Örn. ilk {{ 10 }} lot 2'şer dakika, kalanlar 1'er dakika. Programı uyguladığında
                mevcut <strong>{{ $lotSayisi }}</strong> lotun kapanış zamanları lot no sırasına göre atanır.
                (Hiç teklif almayan lot, kendi kapanışına son 12 saat kala fiyat düşürmeye başlar.)
            </p>

            <form method="post" action="{{ route('yonetim.muzayede.uygula') }}" class="dikey-form">
                @csrf
                <label>İlk lotun kapanış zamanı
                    <input type="datetime-local" name="muzayede_bitis"
                           value="{{ old('muzayede_bitis', $a['muzayede_bitis']) }}" required>
                </label>
                <div class="izgara-form">
                    <label>İlk kaç lot birinci aralıkla?
                        <input type="number" name="muzayede_esik_lot" min="0"
                               value="{{ old('muzayede_esik_lot', $a['muzayede_esik_lot'] ?? 10) }}" required>
                    </label>
                    <label>Birinci aralık (dk)
                        <input type="number" name="muzayede_aralik1" min="0"
                               value="{{ old('muzayede_aralik1', $a['muzayede_aralik1'] ?? 2) }}" required>
                    </label>
                    <label>Sonraki aralık (dk)
                        <input type="number" name="muzayede_aralik2" min="0"
                               value="{{ old('muzayede_aralik2', $a['muzayede_aralik2'] ?? 1) }}" required>
                    </label>
                </div>
                <button type="submit" class="btn btn-dolu">Programı Uygula</button>
            </form>
        </section>
    </main>
@endsection
