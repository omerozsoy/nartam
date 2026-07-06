@extends('layouts.yonetim')

@section('baslik', 'Önizleme')

@section('content')
    <main class="yonetim">
        <div class="yonetim-ust">
            <h1>Önizleme</h1>
            <div class="yonetim-eylem">
                <form method="post" action="{{ route('yonetim.toplu.onayla') }}">
                    @csrf
                    <input type="hidden" name="dosya" value="{{ $dosya }}">
                    <button type="submit" class="btn btn-dolu">Onayla ve Ekle ({{ count($items) }})</button>
                </form>
                <a class="btn" href="{{ route('yonetim.toplu') }}">Vazgeç</a>
            </div>
        </div>

        <p class="alt-not" style="margin-bottom:1rem">
            <strong>{{ count($items) }}</strong> eser eklenecek.
            @if ($atlanan > 0) {{ $atlanan }} satır atlanacak (başlık/fiyat eksik). @endif
            Eklemeden önce kontrol edin; yanlışsa "Vazgeç"e basın.
        </p>

        <section class="kart">
            <table class="tablo">
                <thead>
                <tr><th>Görsel</th><th>Lot</th><th>Başlık</th><th>Alt Başlık</th><th>Başlangıç Fiyatı</th></tr>
                </thead>
                <tbody>
                @foreach ($items as $it)
                    <tr>
                        <td>
                            @if ($it['gorsel'])
                                <img src="{{ $it['gorsel'] }}" alt="" style="width:48px;height:48px;object-fit:cover">
                            @else
                                <span class="alt-not">—</span>
                            @endif
                        </td>
                        <td>{{ $it['lot'] ?? '—' }}</td>
                        <td>{{ $it['baslik'] }}</td>
                        <td>{{ $it['alt'] ?: '—' }}</td>
                        <td>{{ number_format($it['fiyat'], 0, ',', '.') }} ₺</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    </main>
@endsection
