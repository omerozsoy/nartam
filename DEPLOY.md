# Plesk'e Dağıtım (nginx)

Uygulamanın giriş noktası `public/index.php`, statik yolları `/assets/...`. Bu yüzden
**document root `public` klasörünü göstermelidir.** Aksi halde nginx repo kökünde index
bulamaz → 403, ve alt yollar (`/giris`, `/api/ilanlar`) → 404 olur.

## 1) Document root'u `public` yap

Plesk → **Websites & Domains → ozsoy.net → Hosting Settings** (veya **Hosting & DNS →
Hosting Settings**) → **Document root** alanını şu yap:

```
httpdocs/public
```

(Git deposu `httpdocs` içine çekiliyorsa. Deponun çekildiği dizin farklıysa o dizinin
altındaki `public`'i göster.) Kaydet.

## 2) Tüm istekleri index.php'ye yönlendir

**A. Apache stack'i varsa** (Plesk'te Apache + nginx): repodaki `public/.htaccess`
bunu otomatik yapar. Ek işlem gerekmez.

**B. Sadece nginx (PHP-FPM) ise:** Plesk → **Apache & nginx Settings** →
**Additional nginx directives** alanına şunu ekle ve kaydet:

```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

> Not: "Additional nginx directives" alanı görünüyor ama "Additional Apache directives"
> görünmüyorsa, kurulum büyük olasılıkla **sadece nginx**'tir → (B) şıkkını uygula.

## 3) MySQL veritabanını hazırla

**a) Plesk'te veritabanı oluştur:** Plesk → **Databases → Add Database**.
- Veritabanı adı: örn. `nartam`
- Kullanıcı ekle: örn. `nartam_user` + güçlü bir şifre
- Not al: veritabanı adı, kullanıcı, şifre, host (genelde `localhost` / `127.0.0.1`)

**b) `.env` dosyasını oluştur:** Depo kökünde (`.env.example`'ı kopyalayarak) bir `.env`
oluştur ve Plesk'ten aldığın bilgileri yaz:

```
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=nartam
DB_USER=nartam_user
DB_PASS=buraya-sifre
```

> `.env` git'e dahil değildir; sunucuda Plesk **File Manager** ile oluşturabilir veya
> SFTP ile yükleyebilirsin. Alternatif: bu değerleri Plesk'te ortam değişkeni olarak da
> tanımlayabilirsin (ortam değişkenleri `.env`'e göre önceliklidir).

**c) Tabloları oluştur + örnek veri ekle:** kur script'ini bir kez çalıştır:
- **SSH varsa:** `cd ~/httpdocs && php bin/kur.php`
- **SSH yoksa:** Plesk → **Scheduled Tasks → Add Task → Run a PHP script** →
  `bin/kur.php` → **Run Now**

Bu, `admin@nartam.test / admin123` yöneticisini ve 2 örnek ilanı ekler.
(Sıfırlamak için: `php bin/kur.php --sifirla`)

## 4) Git deploy

Plesk → **Git** → **Pull Now** (son commit'i çeker) → **Deploy Now**.
Deployment yolu ile document root aynı depoyu göstermeli.

## Kontrol listesi

- [ ] Document root = `.../public`
- [ ] Yönlendirme: `.htaccess` (Apache) **veya** nginx `try_files` yönergesi
- [ ] `php bin/kur.php` çalıştırıldı (admin@nartam.test / admin123)
- [ ] `data/` yazılabilir
- [ ] Pull Now + Deploy Now

Sonrasında https://www.ozsoy.net açılmalı; `/giris`, `/api/ilanlar` çalışmalı.
