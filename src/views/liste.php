<?php
/** @var \App\Muzayede $muzayede */
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($muzayede->baslik) ?></title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header>
    <h1><?= htmlspecialchars($muzayede->baslik) ?></h1>
    <p>Canlı müzayede — kalemler kapanana kadar teklif verebilirsiniz.</p>
</header>

<main class="kalemler">
    <?php foreach ($muzayede->kalemler() as $kalem): ?>
        <article class="kalem" data-bitis="<?= $kalem->bitisTimestamp() ?>">
            <h2><?= htmlspecialchars($kalem->ad) ?></h2>
            <p class="fiyat">Başlangıç: <?= htmlspecialchars($kalem->fiyatBicimli()) ?></p>
            <p class="sayac" role="timer">--:--</p>
            <button type="button">Teklif Ver</button>
        </article>
    <?php endforeach; ?>
</main>

<script src="/assets/sayac.js"></script>
</body>
</html>
