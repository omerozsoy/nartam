@extends('layouts.app')

@section('baslik', 'Müzayedeler')

@section('content')
    <div class="kap">
        <h1 class="sayfa-baslik">Müzayedeler</h1>

        @forelse ($muzayedeler as $m)
            @php($basladi = $m->baslangic <= now())
            <a class="muzayede-kart" href="{{ route('muzayede.goster', $m) }}">
                <div class="muzayede-no">{{ $m->no }}. Müzayede</div>
                <div class="muzayede-ad">{{ $m->ad }}</div>
                <div class="muzayede-tarih">
                    {{ $m->baslangic->format('d.m.Y H:i') }} · {{ $m->ilanlar_count }} lot
                </div>
                <span class="muzayede-durum {{ $m->aktif ? 'aktif' : '' }}">
                    @if ($m->aktif)
                        Devam Ediyor
                    @elseif (! $basladi)
                        Yakında
                    @else
                        Sona Erdi
                    @endif
                </span>
            </a>
        @empty
            <p class="hesap-bos">Henüz müzayede yok.</p>
        @endforelse
    </div>
@endsection
