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

use App\Kalem;
use App\Muzayede;

date_default_timezone_set('Europe/Istanbul');

// Örnek müzayede verisi (ileride veritabanından gelecek)
$muzayede = new Muzayede('Temmuz Sanat Müzayedesi');
$muzayede->kalemEkle(new Kalem(1, 'Antika Porselen Vazo', 5000, new DateTimeImmutable('+2 minutes')));
$muzayede->kalemEkle(new Kalem(2, 'Yağlı Boya Tablo', 12000, new DateTimeImmutable('+5 minutes')));
$muzayede->kalemEkle(new Kalem(3, 'Gümüş Cep Saati', 3500, new DateTimeImmutable('+8 minutes')));

require __DIR__ . '/../src/views/liste.php';
