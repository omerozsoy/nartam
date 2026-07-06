<?php

declare(strict_types=1);

/*
 * Veritabanı kurulumu ve örnek veri (MySQL veya SQLite — .env'deki DB_DRIVER'a göre).
 *   php bin/kur.php            -> tabloları oluşturur, boşsa örnek veri ekler
 *   php bin/kur.php --sifirla  -> tabloları silip sıfırdan kurar
 */

require __DIR__ . '/../src/onyukleme.php';

use App\Cekirdek\Veritabani;
use App\Depo\IlanDepo;
use App\Depo\KullaniciDepo;

$pdo = Veritabani::pdo();
$driver = Veritabani::driver();
echo "Sürücü: {$driver}\n";

if (in_array('--sifirla', $argv, true)) {
    if ($driver === 'mysql') {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    }
    foreach (['teklifler', 'ilanlar', 'kullanicilar'] as $tablo) {
        $pdo->exec("DROP TABLE IF EXISTS {$tablo}");
    }
    if ($driver === 'mysql') {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
    echo "Tablolar silindi.\n";
}

// Şemayı uygula (ifadeleri tek tek çalıştır — MySQL çoklu ifadeyi tek exec'te sevmez).
semaUygula($pdo, Veritabani::semaYolu());
echo "Şema uygulandı.\n";

$kullaniciDepo = new KullaniciDepo();
$ilanDepo = new IlanDepo();

$mevcut = (int) $pdo->query('SELECT COUNT(*) FROM kullanicilar')->fetchColumn();
if ($mevcut > 0) {
    echo "Veri zaten var, örnek ekleme atlandı. (Sıfırlamak için --sifirla)\n";
    exit(0);
}

// --- Kullanıcılar ---
$mehmetId = $kullaniciDepo->olustur('mehmet@nartam.test', 'Mehmet', password_hash('parola123', PASSWORD_DEFAULT));
$ayseId = $kullaniciDepo->olustur('ayse@nartam.test', 'Ayşe', password_hash('parola123', PASSWORD_DEFAULT));
$kullaniciDepo->olustur('admin@nartam.test', 'Yönetici', password_hash('admin123', PASSWORD_DEFAULT), 'yonetici');
echo "3 kullanıcı eklendi (admin@nartam.test / admin123).\n";

$now = new DateTimeImmutable();

// --- İlan 1: hâlâ düşüş fazında (3 saat önce başladı, 1000 -> 700) ---
$ilanDepo->olustur('Antika Porselen Vazo', 1000, 100, 500, $now->modify('-3 hours'));

// --- İlan 2: açık artırmada (düşüş sırasında teklif geldi, sayaç işliyor) ---
$id2 = $ilanDepo->olustur('Yağlı Boya Tablo', 12000, 500, 8000, $now->modify('-2 hours'));
$ilan2 = $ilanDepo->idIle($id2);

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

/** Şema dosyasını ifade ifade çalıştırır (yorum satırlarını atlar). */
function semaUygula(PDO $pdo, string $dosya): void
{
    $sql = file_get_contents($dosya);
    // -- ile başlayan yorum satırlarını temizle
    $satirlar = array_filter(
        explode("\n", $sql),
        static fn (string $l): bool => !str_starts_with(trim($l), '--')
    );
    $temiz = implode("\n", $satirlar);

    foreach (explode(';', $temiz) as $ifade) {
        $ifade = trim($ifade);
        if ($ifade !== '') {
            $pdo->exec($ifade);
        }
    }
}
