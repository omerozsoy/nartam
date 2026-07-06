<?php
declare(strict_types=1);
?>
<main class="dar-form">
    <h1>Kayıt Ol</h1>
    <form method="post" action="/kayit">
        <?= csrf_alani() ?>
        <label>Ad
            <input type="text" name="ad" required autofocus>
        </label>
        <label>E-posta
            <input type="email" name="eposta" required>
        </label>
        <label>Şifre <small>(en az 6 karakter)</small>
            <input type="password" name="sifre" minlength="6" required>
        </label>
        <button type="submit">Kayıt Ol</button>
    </form>
    <p class="alt-not">Zaten hesabın var mı? <a href="/giris">Giriş yap</a></p>
</main>
