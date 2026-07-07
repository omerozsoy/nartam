@extends('layouts.yonetim')

@section('baslik', $muzayede->exists ? 'Müzayede Düzenle' : 'Yeni Müzayede')

@section('content')
    <main class="yonetim">
        <h1>{{ $muzayede->exists ? 'Müzayede Düzenle' : 'Yeni Müzayede' }}</h1>

        <section class="kart">
            @php($rota = $muzayede->exists ? route('yonetim.muzayede.guncelle', $muzayede) : route('yonetim.muzayede.olustur'))
            @php($fmt = fn ($d) => $d ? $d->format('Y-m-d\TH:i') : '')
            <form method="post" action="{{ $rota }}" class="dikey-form">
                @csrf
                <div class="izgara-form">
                    <label>Müzayede No
                        <input type="text" name="no" value="{{ old('no', $muzayede->no) }}" placeholder="407" required>
                    </label>
                    <label>Müzayede İsmi
                        <input type="text" name="ad" value="{{ old('ad', $muzayede->ad) }}" placeholder="Sanat & Antika" required>
                    </label>
                </div>
                <div class="izgara-form">
                    <label>Başlangıç (teklifler açılır)
                        <input type="datetime-local" name="baslangic" value="{{ old('baslangic', $fmt($muzayede->baslangic)) }}" required>
                    </label>
                    <label>İlk Lotun Kapanışı
                        <input type="datetime-local" name="bitis" value="{{ old('bitis', $fmt($muzayede->bitis)) }}" required>
                    </label>
                </div>
                <p class="alt-not" style="margin:0">Kademeli kapanış: ilk lot yukarıdaki anda; sonraki lotlar aşağıdaki aralıklarla kapanır.</p>
                <div class="izgara-form">
                    <label>İlk kaç lot birinci aralıkla?
                        <input type="number" name="esik_lot" min="0" value="{{ old('esik_lot', $muzayede->esik_lot ?? 10) }}" required>
                    </label>
                    <label>Birinci aralık (dk)
                        <input type="number" name="aralik1" min="0" value="{{ old('aralik1', $muzayede->aralik1 ?? 2) }}" required>
                    </label>
                    <label>Sonraki aralık (dk)
                        <input type="number" name="aralik2" min="0" value="{{ old('aralik2', $muzayede->aralik2 ?? 1) }}" required>
                    </label>
                </div>
                <button type="submit" class="btn btn-dolu">{{ $muzayede->exists ? 'Kaydet' : 'Oluştur' }}</button>
            </form>
        </section>
    </main>
@endsection
