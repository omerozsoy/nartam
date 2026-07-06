# nartam — Müzayede Evi Yazılımı (Laravel 12)

Klasik olmayan, **iki fazlı** bir müzayede sistemi. Bir ilan önce fiyatı düşerek
alıcı bekler; ilk teklif geldiği an klasik açık artırmaya döner.

## Nasıl çalışır

**Faz 1 — Düşen fiyat (Dutch):** İlan başlangıç fiyatından açılır, teklif gelmedikçe
fiyat her saat sabit tutar düşer, **rezerv (taban) fiyatta** durur.

**Faz 2 — Açık artırma (English):** İlk teklif geldiği an **o anki düşmüş fiyat taban**
olur ve **24 saatlik** geri sayım başlar. Sonraki teklifler kademeli artırımla yükselir
(`<1000 → +50`, `<5000 → +100`, `5000+ → +250`). **Anti-snipe:** bitişe son 2 dk kala
gelen teklif sayacı yeniden 2 dk'ya çeker. Süre dolunca en yüksek teklif kazanır.

## Teknoloji

- **Laravel 12** (PHP 8.2+)
- MySQL (üretim) / SQLite (yerel geliştirme)
- Blade şablonları + saf JS (canlı sayaç + polling ile güncelleme)
- Oturum tabanlı kimlik doğrulama, CSRF, yönetici middleware

## Önemli dosyalar

```
app/
  Models/Ilan.php          İki fazlı durum makinesi (Eloquent)
  Models/Teklif.php, User.php
  Enums/Durum.php          DUSUYOR | ACIK_ARTIRMA | KAPANDI
  Services/TeklifServisi.php  Teklif akışı (domen kuralı + transaction)
  Support/Sunum.php        Ilan -> ekran/JSON özeti
  Http/Controllers/        Ilan, Teklif, Kimlik, Yonetim
  Http/Middleware/YoneticiOl.php
database/migrations/       users(+rol), ilanlar, teklifler
database/seeders/          örnek veri (admin + 2 ilan)
resources/views/           layouts, ilanlar, auth, yonetim (Blade)
public/assets/             style.css, sayac.js
routes/web.php
tests/                     Unit\IlanTest, Feature\TeklifTest
```

## Yerel kurulum

```bash
composer install
cp .env.example .env          # DB_CONNECTION=sqlite yerel için yeterli
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve             # http://127.0.0.1:8000
```

Demo yönetici: `admin@nartam.test` / `admin123`

## Test

```bash
php artisan test
```

## Sunucuya (Plesk) dağıtım

Bkz. [DEPLOY.md](DEPLOY.md).

## Yol haritası

- [x] İki fazlı müzayede (düşen fiyat → açık artırma), rezerv, kademeli artırım, anti-snipe
- [x] Laravel 12 + Eloquent + migration'lar
- [x] Kimlik doğrulama, teklif akışı, canlı güncelleme (polling), yönetim paneli
- [ ] Gerçek zamanlı yayın (Laravel Reverb / WebSocket) — polling yerine anlık itme
- [ ] E-posta bildirimleri (teklifin geçildi / kazandın) — Mail + Queue
- [ ] İlan detay sayfası + teklif geçmişi
- [ ] Güçlü yönetim paneli (Filament)
