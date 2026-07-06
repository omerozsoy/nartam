<?php
declare(strict_types=1);
?>
<main class="dar-form">
    <h1>Giriş Yap</h1>
    <form method="post" action="/giris">
        <?= csrf_alani() ?>
        <label>E-posta
            <input type="email" name="eposta" required autofocus>
        </label>
        <label>Şifre
            <input type="password" name="sifre" required>
        </label>
        <button type="submit">Giriş Yap</button>
    </form>
    <p class="alt-not">Hesabın yok mu? <a href="/kayit">Kayıt ol</a></p>
    <p class="alt-not ipucu">Demo: <code>admin@nartam.test</code> / <code>admin123</code></p>
</main>
