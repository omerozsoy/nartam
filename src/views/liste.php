<?php
/**
 * @var array[] $ilanlar  Sunum::ilan() özetleri
 * @var array|null $kullanici
 */
declare(strict_types=1);
?>
<header class="sayfa-baslik">
    <h1>nartam Müzayede</h1>
    <p>Teklif gelene kadar fiyat düşer; ilk teklifle açık artırma başlar.</p>
</header>

<main class="kalemler" id="ilanlar">
    <?php foreach ($ilanlar as $ilan): ?>
        <article
            class="kalem durum-<?= e($ilan['durum']) ?>"
            data-id="<?= (int) $ilan['id'] ?>"
            data-durum="<?= e($ilan['durum']) ?>"
            <?php if ($ilan['bitisTs'] !== null): ?>data-bitis="<?= (int) $ilan['bitisTs'] ?>"<?php endif; ?>
            <?php if ($ilan['sonrakiDususTs'] !== null): ?>data-sonraki-dusus="<?= (int) $ilan['sonrakiDususTs'] ?>"<?php endif; ?>
            data-min="<?= (int) $ilan['minTeklif'] ?>"
        >
            <span class="rozet"><?= e($ilan['durumEtiket']) ?></span>
            <h2><?= e($ilan['baslik']) ?></h2>

            <p class="fiyat" data-alan="fiyat"><?= e($ilan['guncelFiyatBicim']) ?></p>

            <p class="sayac-etiket" data-alan="sayac-etiket">
                <?php if ($ilan['durum'] === 'dusuyor'): ?>
                    Sonraki düşüşe
                <?php elseif ($ilan['durum'] === 'acik_artirma'): ?>
                    Bitişe kalan
                <?php else: ?>
                    Kazanan: <?= e($ilan['sonTeklifSahibi'] ?? '—') ?>
                <?php endif; ?>
            </p>
            <?php if ($ilan['durum'] !== 'kapandi'): ?>
                <p class="sayac" role="timer" data-alan="sayac">--:--</p>
            <?php endif; ?>

            <?php if ($ilan['durum'] !== 'kapandi'): ?>
                <?php if ($kullanici !== null): ?>
                    <form class="teklif-form" data-alan="teklif-form">
                        <?= csrf_alani() ?>
                        <input type="hidden" name="ilan_id" value="<?= (int) $ilan['id'] ?>">
                        <input
                            type="number" name="miktar" step="1"
                            min="<?= (int) $ilan['minTeklif'] ?>"
                            value="<?= (int) $ilan['minTeklif'] ?>"
                            data-alan="miktar" required
                        >
                        <button type="submit">
                            <?= $ilan['durum'] === 'dusuyor' ? 'Bu Fiyata Al' : 'Teklif Ver' ?>
                        </button>
                        <span class="teklif-mesaj" data-alan="teklif-mesaj"></span>
                    </form>
                <?php else: ?>
                    <a class="baglanti-buton dolu" href="/giris">Teklif için giriş yap</a>
                <?php endif; ?>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</main>

<script src="/assets/sayac.js"></script>
