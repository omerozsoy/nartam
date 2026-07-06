<?php
/**
 * @var string $baslik
 * @var string $icerik  (güvenli, önceden render edilmiş HTML)
 * @var array|null $kullanici
 */
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($baslik) ?> — nartam</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<nav class="ust-bar">
    <a class="marka" href="/">nartam</a>
    <div class="ust-baglantilar">
        <?php if ($kullanici !== null): ?>
            <?php if (($kullanici['rol'] ?? '') === 'yonetici'): ?>
                <a href="/yonetim">Yönetim</a>
            <?php endif; ?>
            <span class="kullanici-ad"><?= e($kullanici['ad']) ?></span>
            <form method="post" action="/cikis" class="satir-ici">
                <?= csrf_alani() ?>
                <button type="submit" class="baglanti-buton">Çıkış</button>
            </form>
        <?php else: ?>
            <a href="/giris">Giriş</a>
            <a href="/kayit" class="vurgu-baglanti">Kayıt Ol</a>
        <?php endif; ?>
    </div>
</nav>

<?php foreach (flash_al() as $f): ?>
    <div class="flash flash-<?= e($f['tur']) ?>"><?= e($f['mesaj']) ?></div>
<?php endforeach; ?>

<?= $icerik ?>

</body>
</html>
