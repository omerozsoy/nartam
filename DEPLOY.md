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

## 3) Veritabanını hazırla

SQLite dosyası `data/` içinde ve git'e dahil değil. Şema ilk istekte otomatik oluşur,
ama **örnek veri + yönetici hesabı** için bir kez kur script'ini çalıştır:

- **SSH varsa:**
  ```bash
  cd ~/httpdocs        # deponun kökü
  php bin/kur.php
  ```
- **SSH yoksa:** Plesk → **Scheduled Tasks → Add Task** → "Run a PHP script" →
  `bin/kur.php` seç → **Run Now**.

`data/` dizininin web kullanıcısı (psacln) tarafından yazılabilir olduğundan emin ol
(genelde otomatik oluşur; sorun olursa Plesk File Manager'da `data/` izinlerini kontrol et).

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
