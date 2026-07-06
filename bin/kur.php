<?php

declare(strict_types=1);

/*
 * Veritabanı kurulumu ve örnek veri.
 *   php bin/kur.php            -> tablolar yoksa oluşturur, boşsa örnek veri ekler
 *   php bin/kur.php --sifirla  -> tabloları silip sıfırdan kurar
 */

require __DIR__ . '/../src/onyukleme.php';

use App\Cekirdek\Veritabani;
use App\Depo\IlanDepo;
use App\Depo\KullaniciDepo;
use App\Ilan;

$pdo = Veritabani::pdo();

if (in_array('--sifirla', $argv, true)) {
    foreach (['teklifler', 'ilanlar', 'kullanicilar'] as $tablo) {
        $pdo->exec("DROP TABLE IF EXISTS {$tablo}");
    }
    echo "Tablolar silindi.\n";
}

$pdo->exec(file_get_contents(__DIR__ . '/../db/schema.sql'));
echo "Şema uygulandı.\n";

$kullaniciDepo = new KullaniciDepo();
$ilanDepo = new IlanDepo();

$mevcut = (int) $pdo->query('SELECT COUNT(*) FROM kullanicilar')->fetchColumn();
if ($mevcut > 0) {
    echo "Veri zaten var, örnek ekleme atlandı. (Sıfırlamak için --sifirla)\n";
    exit(0);
}

// --- Kullanıcılar ---
$adminId = $kullaniciDepo->olustur('admin@nartam.test', 'Yönetici', password_hash('admin123', PASSWORD_DEFAULT), 'yonetici');
$mehmetId = $kullaniciDepo->olustur('mehmet@nartam.test', 'Mehmet', password_hash('parola123', PASSWORD_DEFAULT));
$ayseId = $kullaniciDepo->olustur('ayse@nartam.test', 'Ayşe', password_hash('parola123', PASSWORD_DEFAULT));
echo "3 kullanıcı eklendi (admin@nartam.test / admin123).\n";

$now = new DateTimeImmutable();

// --- İlan 1: hâlâ düşüş fazında (3 saat önce başladı, 1000 -> 700) ---
$ilanDepo->olustur('Antika Porselen Vazo', 1000, 100, 500, $now->modify('-3 hours'));

// --- İlan 2: açık artırmada (düşüş sırasında teklif geldi, sayaç işliyor) ---
$id2 = $ilanDepo->olustur('Yağlı Boya Tablo', 12000, 500, 8000, $now->modify('-2 hours'));
$ilan2 = $ilanDepo->idIle($id2);

// -90 dk'da fiyat 12000'di; Mehmet o tabanı aldı, Ayşe üstüne çıktı.
$t1 = $now->modify('-90 minutes');
$ilan2->teklifVer('Mehmet', 12000, $t1);
$ilanDepo->guncelle($ilan2);
$ilanDepo->teklifKaydet($id2, $mehmetId, 12000, $t1);

$t2 = $now->modify('-40 minutes');
$ilan2->teklifVer('Ayşe', 12500, $t2);
$ilanDepo->guncelle($ilan2);
$ilanDepo->teklifKaydet($id2, $ayseId, 12500, $t2);

echo "2 örnek ilan eklendi.\n";
echo "Kurulum tamam.\n";
