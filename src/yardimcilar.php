<?php

declare(strict_types=1);

/*
 * Genel yardımcı fonksiyonlar (namespace'siz, global).
 */

/** HTML kaçışı. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/** Para biçimi. */
function para(int $tutar): string
{
    return number_format($tutar, 0, ',', '.') . ' ₺';
}

/** Yönlendir ve çık. */
function yonlendir(string $yol): never
{
    header('Location: ' . $yol);
    exit;
}

/** JSON yanıtı gönder ve çık. */
function json_yanit(mixed $veri, int $kod = 200): never
{
    http_response_code($kod);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($veri, JSON_UNESCAPED_UNICODE);
    exit;
}

// --- CSRF ---

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['_csrf'];
}

function csrf_alani(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_dogrula(): void
{
    $gelen = $_POST['_csrf'] ?? '';
    if (!is_string($gelen) || !hash_equals(csrf_token(), $gelen)) {
        http_response_code(419);
        exit('Oturum doğrulaması başarısız (CSRF).');
    }
}

// --- Flash mesajları ---

function flash_koy(string $tur, string $mesaj): void
{
    $_SESSION['_flash'][] = ['tur' => $tur, 'mesaj' => $mesaj];
}

/** @return list<array{tur: string, mesaj: string}> */
function flash_al(): array
{
    $mesajlar = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);

    return $mesajlar;
}
