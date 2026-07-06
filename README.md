# nartam — Müzayede Evi Yazılımı

Klasik olmayan, **iki fazlı** bir müzayede sistemi. Bir ilan önce fiyatı düşerek
alıcı bekler; ilk teklif geldiği an klasik açık artırmaya döner.

## Nasıl çalışır

**Faz 1 — Düşen fiyat (Dutch):**
- İlan başlangıç fiyatından açılır (örn. 1000 ₺).
- Teklif gelmedikçe fiyat her saat sabit tutar düşer (örn. −100 ₺/saat).
- Fiyat **rezerv (taban) fiyatın** altına inmez; orada bekler.

**Faz 2 — Açık artırma (English), ilk teklifle:**
- İlk teklif geldiği an, **o anki düşmüş fiyat taban** olur (örn. 700'e düşmüşse
  açık artırma 700'den başlar).
- İlk tekliften itibaren **24 saatlik geri sayım** başlar.
- Sonraki teklifler **kademeli artırım adımıyla** yükselir:
  `<1000 → +50`, `<5000 → +100`, `5000+ → +250`.
- **Anti-snipe:** bitişe son 2 dakika kala gelen teklif, sayacı yeniden 2 dakikaya
  çeker — son saniye kapışları önlenir.
- Süre dolunca en yüksek teklif kazanır.

## Klasör yapısı

```
public/           Web kök dizini
  index.php       Giriş noktası + örnek ilanlar
  assets/         style.css, sayac.js (iki fazlı canlı sayaç)
src/              Uygulama kodu (App\ namespace)
  Ilan.php        İlan yaşam döngüsü / durum makinesi (Dutch → English)
  Durum.php       DUSUYOR | ACIK_ARTIRMA | KAPANDI
  views/          HTML şablonları
tests/
  ilan_test.php   Durum makinesi testleri (php tests/ilan_test.php)
composer.json     PSR-4 autoload tanımı
```

## Çalıştırma

Composer olmadan da çalışır (basit autoloader devrede):

```bash
php -S localhost:8000 -t public
```

Tarayıcıda: http://localhost:8000

## Test

```bash
php tests/ilan_test.php
```

## Yol haritası

- [x] İki fazlı ilan modeli (düşen fiyat → açık artırma)
- [x] Rezerv taban, kademeli artırım, anti-snipe
- [x] İki fazlı canlı sayaç (ön yüz)
- [ ] Veritabanı (ilanlar, teklifler, kullanıcılar)
- [ ] Gerçek teklif verme akışı (form + sunucu doğrulaması)
- [ ] Canlı güncelleme (WebSocket / SSE) — teklifler anlık yayılsın
- [ ] Yönetim paneli (ilan oluşturma/yönetme)
- [ ] Kullanıcı kayıt / kimlik doğrulama
```
