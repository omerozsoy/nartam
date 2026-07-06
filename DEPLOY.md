# Plesk'e Dağıtım (Laravel 12 + nginx + MySQL)

Laravel'in giriş noktası `public/index.php`. Document root **`public`** klasörünü
göstermelidir. Ayrıca sunucuda `composer install`, `.env` + `APP_KEY` ve `migrate`
gerekir (bunlar git'e dahil değildir).

## 1) Kodu çek

Plesk → **Git** → depo bağlı → **Pull Now**. (Kod `httpdocs` altına gelir.)

## 2) Document root'u `public` yap

Plesk → **Websites & Domains → ozsoy.net → Hosting Settings** → **Document root**:

```
httpdocs/public
```

## 3) Bağımlılıkları kur (composer)

`vendor/` git'e dahil değildir; sunucuda kurulmalı.
- **SSH varsa:** `cd ~/httpdocs && composer install --no-dev --optimize-autoloader`
- **SSH yoksa:** Plesk → **PHP Composer** aracıyla depo kökünde `install` çalıştır.

> PHP sürümü **8.2+** olmalı (Plesk → PHP Settings).

## 4) `.env` oluştur ve anahtar üret

Depo kökünde `.env` oluştur (`.env.example`'ı kopyala) ve doldur:

```
APP_NAME=nartam
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.ozsoy.net

APP_KEY=            # bir sonraki adımda üretilecek

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nartam
DB_USERNAME=nartam_user
DB_PASSWORD=buraya-sifre
```

Sonra uygulama anahtarını üret:
```bash
php artisan key:generate
```

**MySQL veritabanı:** Plesk → **Databases → Add Database** ile `nartam` veritabanı +
kullanıcı oluştur; bilgileri yukarıdaki `.env`'e yaz.

## 5) Tabloları oluştur + örnek veri

```bash
php artisan migrate --seed --force
```

(`admin@nartam.test / admin123` yöneticisi ve 2 örnek ilan eklenir.
Yeniden sıfırlamak için: `php artisan migrate:fresh --seed --force`.)

## 6) İzinler ve önbellek

`storage/` ve `bootstrap/cache/` web kullanıcısı tarafından yazılabilir olmalı
(Plesk'te genelde otomatik). Üretimde önbellek:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Not: `.env` veya route değişince `php artisan optimize:clear` ile önbelleği temizle.

## 7) Yönlendirme

Laravel `public/.htaccess` ile gelir; **Apache stack'i** varsa ek işlem gerekmez.
**Sadece nginx** ise Plesk → **Apache & nginx Settings → Additional nginx directives**:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Kontrol listesi

- [ ] Document root = `httpdocs/public`
- [ ] `composer install --no-dev` çalıştı (vendor/ oluştu)
- [ ] `.env` dolu + `php artisan key:generate` (APP_KEY set)
- [ ] MySQL DB oluşturuldu, `.env`'e yazıldı
- [ ] `php artisan migrate --seed --force`
- [ ] `storage/` ve `bootstrap/cache/` yazılabilir
- [ ] Yönlendirme: `.htaccess` (Apache) veya nginx `try_files`

## Otomatik dağıtım (opsiyonel)

Plesk → **Git → Deployment actions** ile Pull sonrası şunları otomatikleştirebilirsin:
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```
