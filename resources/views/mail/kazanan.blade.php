<x-mail::message>
# Tebrikler {{ $ad }}!

**{{ $baslik }}**@if ($lotNo) (Lot {{ $lotNo }})@endif müzayedesini kazandınız.

Kazanan teklifiniz: **{{ $tutar }}**

Ödeme ve teslimat için hesabınızdaki **Kazandıklarım** bölümünden devam edebilirsiniz.

<x-mail::button :url="config('app.url') . '/hesabim'">
Hesabıma Git
</x-mail::button>

Teşekkürler,<br>
{{ config('app.name') }}
</x-mail::message>
