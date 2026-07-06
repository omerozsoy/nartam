<?php

declare(strict_types=1);

/*
 * Müzayede evi — giriş noktası.
 * composer kurulana kadar basit bir PSR-4 autoloader kullanıyoruz;
 * `composer install` yapınca vendor/autoload.php'ye geçebilirsin.
 */
$autoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (str_starts_with($class, $prefix)) {
            $file = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require $file;
            }
        }
    });
}

use App\Ilan;

date_default_timezone_set('Europe/Istanbul');
$now = new DateTimeImmutable();

/*
 * Örnek ilanlar (ileride veritabanından gelecek). Her iki fazı da göstermek için
 * başlangıç zamanlarını geçmişe alıyoruz.
 */
$ilanlar = [];

// 1) Düşüş fazında: 3 saat önce başladı, fiyat 1000 → 700.
$ilanlar[] = new Ilan('Antika Porselen Vazo', 1000, 100, 500, $now->modify('-3 hours'));

// 2) Açık artırmada: düşüş sırasında teklif geldi, 24 saatlik sayaç işliyor.
//    -90 dk'da fiyat henüz 12000'di; ilk teklif o tabanı alır, üzerine çıkıldı.
$acikArtirma = new Ilan('Yağlı Boya Tablo', 12000, 500, 8000, $now->modify('-2 hours'));
$acikArtirma->teklifVer('mehmet', 12000, $now->modify('-90 minutes'));
$acikArtirma->teklifVer('ayse', 12500, $now->modify('-40 minutes'));
$ilanlar[] = $acikArtirma;

require __DIR__ . '/../src/views/liste.php';
