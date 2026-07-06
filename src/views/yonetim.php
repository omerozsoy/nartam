<?php
/**
 * @var array[] $ilanlar
 */
declare(strict_types=1);
?>
<main class="yonetim">
    <h1>Yönetim Paneli</h1>

    <section class="kart">
        <h2>Yeni İlan</h2>
        <form method="post" action="/yonetim/ilan" class="izgara-form">
            <?= csrf_alani() ?>
            <label>Başlık
                <input type="text" name="baslik" required>
            </label>
            <label>Başlangıç Fiyatı (₺)
                <input type="number" name="baslangic_fiyati" min="1" value="1000" required>
            </label>
            <label>Saatlik Düşüş (₺)
                <input type="number" name="saatlik_dusus" min="1" value="100" required>
            </label>
            <label>Rezerv (Taban) Fiyat (₺)
                <input type="number" name="rezerv_fiyat" min="0" value="500" required>
            </label>
            <button type="submit">İlan Oluştur</button>
        </form>
        <p class="alt-not">İlan hemen "düşen fiyat" fazında başlar; ilk teklifle açık artırmaya döner.</p>
    </section>

    <section class="kart">
        <h2>Mevcut İlanlar</h2>
        <table class="tablo">
            <thead>
            <tr><th>#</th><th>Başlık</th><th>Durum</th><th>Güncel Fiyat</th><th>Son Teklif</th></tr>
            </thead>
            <tbody>
            <?php foreach ($ilanlar as $ilan): ?>
                <tr>
                    <td><?= (int) $ilan['id'] ?></td>
                    <td><?= e($ilan['baslik']) ?></td>
                    <td><span class="rozet rozet-<?= e($ilan['durum']) ?>"><?= e($ilan['durumEtiket']) ?></span></td>
                    <td><?= e($ilan['guncelFiyatBicim']) ?></td>
                    <td><?= e($ilan['sonTeklifSahibi'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
