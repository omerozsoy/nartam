# nartam — Müzayede Evi Yazılımı

Canlı müzayede yönetimi için PHP tabanlı web uygulaması. Her kalem (lot) için geri sayım sayacı ile teklif süresi takibi.

## Özellikler (başlangıç)

- Müzayede oturumu ve kalem (lot) modeli
- Kalem başına canlı geri sayım **sayacı** (JavaScript, saniyede bir güncellenir)
- Süre bitince kalem otomatik "KAPANDI" durumuna geçer, teklif butonu kilitlenir
- Son 30 saniyede sayaç kırmızıya döner (kritik uyarı)

## Klasör yapısı

```
public/           Web kök dizini (sunucu buraya bakar)
  index.php       Giriş noktası
  assets/         style.css, sayac.js
src/              Uygulama kodu (App\ namespace)
  Muzayede.php    Müzayede oturumu
  Kalem.php       Satışa çıkan kalem (lot)
  views/          HTML şablonları
composer.json     PSR-4 autoload tanımı
```

## Çalıştırma

Composer olmadan da çalışır (basit autoloader devrede):

```bash
php -S localhost:8000 -t public
```

Ardından tarayıcıda: http://localhost:8000

İleride bağımlılık eklenince:

```bash
composer install
```

## Yol haritası

- [ ] Veritabanı (kalemler, teklifler, kullanıcılar)
- [ ] Gerçek teklif verme akışı + sunucu tarafı sayaç doğrulaması
- [ ] Canlı güncelleme (WebSocket / SSE) ile teklifler anlık yayılsın
- [ ] Yönetim paneli (müzayede ve kalem yönetimi)
- [ ] Kullanıcı kayıt / kimlik doğrulama
