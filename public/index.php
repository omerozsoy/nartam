<?php

declare(strict_types=1);

// php -S için: var olan statik dosyaları (assets) doğrudan sun.
if (PHP_SAPI === 'cli-server') {
    $yol = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($yol !== '/' && is_file(__DIR__ . $yol)) {
        return false;
    }
}

require __DIR__ . '/../src/onyukleme.php';

use App\Cekirdek\Gorunum;
use App\Depo\IlanDepo;
use App\Kimlik;
use App\Sunum;
use App\TeklifServisi;

$kimlik = new Kimlik();
$ilanDepo = new IlanDepo();

$method = $_SERVER['REQUEST_METHOD'];
$yol = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/') ?: '/';
$rota = $method . ' ' . $yol;

$kullanici = $kimlik->mevcut();

/** Giriş zorunlu; değilse yönlendir. */
$girisGerekli = static function () use ($kullanici): array {
    if ($kullanici === null) {
        flash_koy('hata', 'Bu işlem için giriş yapmalısınız.');
        yonlendir('/giris');
    }
    return $kullanici;
};

/** Yönetici zorunlu. */
$yoneticiGerekli = static function () use ($kimlik): void {
    if (!$kimlik->yonetici()) {
        http_response_code(403);
        exit('Yetkisiz.');
    }
};

switch ($rota) {
    // --- Ana sayfa: ilan listesi ---
    case 'GET /':
        $now = new DateTimeImmutable();
        $ilanlar = array_map(static fn ($i) => Sunum::ilan($i, $now), $ilanDepo->tumu());
        echo Gorunum::sayfa('liste', ['ilanlar' => $ilanlar], 'nartam Müzayede', $kullanici);
        break;

    // --- Canlı güncelleme API'si (polling) ---
    case 'GET /api/ilanlar':
        $now = new DateTimeImmutable();
        json_yanit(array_map(static fn ($i) => Sunum::ilan($i, $now), $ilanDepo->tumu()));
        // no break (json_yanit exit eder)

    // --- Kayıt ---
    case 'GET /kayit':
        echo Gorunum::sayfa('kayit', [], 'Kayıt Ol', $kullanici);
        break;

    case 'POST /kayit':
        csrf_dogrula();
        try {
            $kimlik->kayit($_POST['eposta'] ?? '', $_POST['ad'] ?? '', $_POST['sifre'] ?? '');
            flash_koy('basari', 'Hoş geldiniz!');
            yonlendir('/');
        } catch (\DomainException $e) {
            flash_koy('hata', $e->getMessage());
            yonlendir('/kayit');
        }
        // no break

    // --- Giriş ---
    case 'GET /giris':
        echo Gorunum::sayfa('giris', [], 'Giriş Yap', $kullanici);
        break;

    case 'POST /giris':
        csrf_dogrula();
        try {
            $kimlik->giris($_POST['eposta'] ?? '', $_POST['sifre'] ?? '');
            flash_koy('basari', 'Giriş yapıldı.');
            yonlendir('/');
        } catch (\DomainException $e) {
            flash_koy('hata', $e->getMessage());
            yonlendir('/giris');
        }
        // no break

    // --- Çıkış ---
    case 'POST /cikis':
        csrf_dogrula();
        $kimlik->cikis();
        yonlendir('/');
        // no break

    // --- Teklif ver (AJAX veya form) ---
    case 'POST /teklif':
        csrf_dogrula();
        $u = $girisGerekli();
        $ilanId = (int) ($_POST['ilan_id'] ?? 0);
        $miktar = (int) ($_POST['miktar'] ?? 0);
        try {
            $ilan = (new TeklifServisi())->teklifVer($ilanId, (int) $u['id'], $u['ad'], $miktar);
            $ozet = Sunum::ilan($ilan, new DateTimeImmutable());
            if (($_POST['ajax'] ?? '') === '1') {
                json_yanit(['ok' => true, 'ilan' => $ozet]);
            }
            flash_koy('basari', 'Teklifiniz alındı: ' . para($miktar));
            yonlendir('/');
        } catch (\DomainException $e) {
            if (($_POST['ajax'] ?? '') === '1') {
                json_yanit(['ok' => false, 'hata' => $e->getMessage()], 422);
            }
            flash_koy('hata', $e->getMessage());
            yonlendir('/');
        }
        // no break

    // --- Yönetim paneli ---
    case 'GET /yonetim':
        $yoneticiGerekli();
        $now = new DateTimeImmutable();
        $ilanlar = array_map(static fn ($i) => Sunum::ilan($i, $now), $ilanDepo->tumu());
        echo Gorunum::sayfa('yonetim', ['ilanlar' => $ilanlar], 'Yönetim', $kullanici);
        break;

    case 'POST /yonetim/ilan':
        $yoneticiGerekli();
        csrf_dogrula();
        $baslik = trim($_POST['baslik'] ?? '');
        $baslangic = (int) ($_POST['baslangic_fiyati'] ?? 0);
        $dusus = (int) ($_POST['saatlik_dusus'] ?? 0);
        $rezerv = (int) ($_POST['rezerv_fiyat'] ?? 0);
        if ($baslik === '' || $baslangic <= 0 || $dusus <= 0 || $rezerv < 0 || $rezerv > $baslangic) {
            flash_koy('hata', 'İlan bilgileri geçersiz (rezerv, başlangıç fiyatını aşamaz).');
            yonlendir('/yonetim');
        }
        $ilanDepo->olustur($baslik, $baslangic, $dusus, $rezerv, new DateTimeImmutable());
        flash_koy('basari', 'İlan oluşturuldu: ' . $baslik);
        yonlendir('/yonetim');
        // no break

    default:
        http_response_code(404);
        echo Gorunum::sayfa('hata404', [], 'Bulunamadı', $kullanici);
}
