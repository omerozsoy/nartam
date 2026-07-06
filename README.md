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

## Özellikler

- İki fazlı ilan yaşam döngüsü (düşen fiyat → açık artırma)
- Kullanıcı kayıt / giriş (oturum + `password_hash`, CSRF korumalı formlar)
- Teklif verme akışı (AJAX; domen kuralları sunucuda doğrulanır)
- **Canlı güncelleme:** ana sayfa `/api/ilanlar`'ı periyodik yoklar; fiyat, durum ve
  sayaçlar sayfa yenilemeden güncellenir
- Yönetim paneli (yönetici): yeni ilan oluşturma, ilan listesi
- SQLite veritabanı (kurulum gerektirmez)

## Klasör yapısı

```
public/
  index.php          Ön denetleyici (router) + statik dosya geçişi
  assets/            style.css, sayac.js (sayaç + polling + AJAX teklif)
src/
  onyukleme.php      Autoloader, oturum, yapılandırma
  yardimcilar.php    e(), para(), CSRF, flash, yönlendirme
  Ilan.php           İlan durum makinesi (Dutch → English)
  Durum.php          DUSUYOR | ACIK_ARTIRMA | KAPANDI
  Kimlik.php         Kayıt / giriş / çıkış
  TeklifServisi.php  Teklif akışı (domen + kalıcılaştırma, transaction)
  Sunum.php          Ilan -> ekran/JSON özeti
  Cekirdek/          Veritabani (PDO), Gorunum (view), Config (.env)
  Depo/              IlanDepo, KullaniciDepo (repository)
  views/             duzen, liste, giris, kayit, yonetim, hata404
db/
  schema.mysql.sql   MySQL şeması (üretim)
  schema.sqlite.sql  SQLite şeması (yerel)
.env.example         Ortam yapılandırma örneği (.env git dışı)
bin/kur.php          Veritabanı kurulumu + örnek veri (her iki sürücü)
tests/ilan_test.php  Durum makinesi testleri
data/                SQLite dosyası (git dışı)
```

## Kurulum ve çalıştırma

Veritabanı sürücüsü `.env`'deki `DB_DRIVER` ile seçilir: **mysql** (üretim) veya
**sqlite** (yerel geliştirme, kurulum gerektirmez).

```bash
# 1) Ortam dosyasını hazırla
cp .env.example .env
#   Yerel geliştirme için hızlı yol: .env içinde DB_DRIVER=sqlite yeterli.
#   MySQL için DB_HOST/DB_NAME/DB_USER/DB_PASS doldur.

# 2) Veritabanını kur + örnek veri ekle
php bin/kur.php

# 3) Sunucuyu başlat (router script olarak index.php)
php -S localhost:8000 -t public public/index.php
```

Tarayıcıda: http://localhost:8000

Demo yönetici: `admin@nartam.test` / `admin123`
Veritabanını sıfırlamak: `php bin/kur.php --sifirla`
Sunucuya (Plesk) dağıtım: bkz. [DEPLOY.md](DEPLOY.md)

## Test

```bash
php tests/ilan_test.php
```

## Yol haritası

- [x] İki fazlı ilan modeli (düşen fiyat → açık artırma)
- [x] Rezerv taban, kademeli artırım, anti-snipe
- [x] İki fazlı canlı sayaç (ön yüz)
- [x] Veritabanı (SQLite) — ilanlar, teklifler, kullanıcılar
- [x] Kullanıcı kayıt / giriş (oturum, CSRF)
- [x] Teklif verme akışı (AJAX + sunucu doğrulaması)
- [x] Canlı güncelleme (polling)
- [x] Yönetim paneli (ilan oluşturma)
- [ ] Gerçek zamanlı yayın (WebSocket / SSE) — polling yerine anlık itme
- [ ] Teklif geçmişi görünümü (ilan detay sayfası)
- [ ] E-posta bildirimleri (teklif geçildi / kazandın)
- [ ] Testleri PHPUnit'e taşımak
```
