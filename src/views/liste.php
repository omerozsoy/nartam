<?php
/**
 * @var \App\Ilan[] $ilanlar
 * @var \DateTimeImmutable $now
 */
declare(strict_types=1);

use App\Durum;

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nartam — Müzayede</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header>
    <h1>nartam Müzayede</h1>
    <p>Teklif gelene kadar fiyat düşer; ilk teklifle açık artırma başlar.</p>
</header>

<main class="kalemler">
    <?php foreach ($ilanlar as $ilan): ?>
        <?php
        $durum = $ilan->durum($now);
        $bitis = $ilan->bitisZamani();
        $sonrakiDusus = $ilan->sonrakiDususZamani($now);
        ?>
        <article
            class="kalem durum-<?= $durum->value ?>"
            data-durum="<?= $durum->value ?>"
            <?php if ($bitis !== null): ?>data-bitis="<?= $bitis->getTimestamp() ?>"<?php endif; ?>
            <?php if ($sonrakiDusus !== null): ?>data-sonraki-dusus="<?= $sonrakiDusus->getTimestamp() ?>"<?php endif; ?>
        >
            <span class="rozet"><?= htmlspecialchars($durum->etiket()) ?></span>
            <h2><?= htmlspecialchars($ilan->baslik) ?></h2>

            <p class="fiyat">
                <?= number_format($ilan->guncelFiyat($now), 0, ',', '.') ?> ₺
            </p>

            <?php if ($durum === Durum::DUSUYOR): ?>
                <p class="sayac-etiket">Sonraki düşüşe</p>
                <p class="sayac" role="timer">--:--</p>
            <?php elseif ($durum === Durum::ACIK_ARTIRMA): ?>
                <p class="sayac-etiket">Bitişe kalan</p>
                <p class="sayac" role="timer">--:--:--</p>
            <?php else: ?>
                <p class="sayac-etiket">
                    Kazanan: <?= htmlspecialchars($ilan->sonTeklifSahibi() ?? '—') ?>
                </p>
            <?php endif; ?>

            <button type="button" <?= $durum === Durum::KAPANDI ? 'disabled' : '' ?>>
                <?= $durum === Durum::DUSUYOR ? 'Bu Fiyata Al' : 'Teklif Ver' ?>
            </button>
        </article>
    <?php endforeach; ?>
</main>

<script src="/assets/sayac.js"></script>
</body>
</html>
