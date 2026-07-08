@extends('layouts.yonetim')

@section('baslik', $muzayede->exists ? 'Müzayede Düzenle' : 'Yeni Müzayede')

@section('content')
    <main class="yonetim">
        <h1>{{ $muzayede->exists ? 'Müzayede Düzenle' : 'Yeni Müzayede' }}</h1>

        <section class="kart">
            @php($rota = $muzayede->exists ? route('yonetim.muzayede.guncelle', $muzayede) : route('yonetim.muzayede.olustur'))
            @php($bd = old('baslangic_tarih', $muzayede->baslangic?->format('Y-m-d')))
            @php($bs = old('baslangic_saat', $muzayede->baslangic?->format('H')))
            @php($bm = old('baslangic_dk', $muzayede->baslangic?->format('i')))
            @php($kd = old('bitis_tarih', $muzayede->bitis?->format('Y-m-d')))
            @php($ks = old('bitis_saat', $muzayede->bitis?->format('H')))
            @php($km = old('bitis_dk', $muzayede->bitis?->format('i')))
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
                <label>Başlangıç (teklifler açılır)
                    <span class="tarih-grup">
                        <input type="date" name="baslangic_tarih" value="{{ $bd }}" required>
                        <input type="number" name="baslangic_saat" min="0" max="23" value="{{ $bs }}" placeholder="SS" required>
                        <span class="tarih-ikinokta">:</span>
                        <input type="number" name="baslangic_dk" min="0" max="59" value="{{ $bm }}" placeholder="DD" required>
                    </span>
                </label>
                <label>İlk Lotun Kapanışı
                    <span class="tarih-grup">
                        <input type="date" name="bitis_tarih" value="{{ $kd }}" required>
                        <input type="number" name="bitis_saat" min="0" max="23" value="{{ $ks }}" placeholder="SS" required>
                        <span class="tarih-ikinokta">:</span>
                        <input type="number" name="bitis_dk" min="0" max="59" value="{{ $km }}" placeholder="DD" required>
                    </span>
                </label>
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
