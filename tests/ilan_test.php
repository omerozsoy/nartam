<?php

declare(strict_types=1);

/*
 * Basit assert tabanlı test (henüz PHPUnit yok).
 * Çalıştır: php tests/ilan_test.php
 */

require __DIR__ . '/../src/Durum.php';
require __DIR__ . '/../src/Ilan.php';

use App\Durum;
use App\Ilan;

$gecen = 0;
$basari = 0;

function kontrol(string $ad, callable $test): void
{
    global $gecen, $basari;
    $gecen++;
    try {
        $test();
        $basari++;
        echo "  ✓ {$ad}\n";
    } catch (Throwable $e) {
        echo "  ✗ {$ad} — {$e->getMessage()}\n";
    }
}

function esit($beklenen, $gercek, string $mesaj = ''): void
{
    if ($beklenen !== $gercek) {
        throw new RuntimeException(sprintf('%s beklenen=%s gerçek=%s', $mesaj, var_export($beklenen, true), var_export($gercek, true)));
    }
}

function firlatir(callable $fn, string $mesaj = 'istisna beklendi'): void
{
    try {
        $fn();
    } catch (Throwable) {
        return;
    }
    throw new RuntimeException($mesaj);
}

$t0 = new DateTimeImmutable('2026-07-06 12:00:00');
$yeniIlan = static fn (): Ilan => new Ilan('Test Ürünü', 1000, 100, 500, $t0);

echo "Düşüş fazı:\n";
kontrol('başlangıçta fiyat 1000 ve durum DUSUYOR', function () use ($yeniIlan, $t0) {
    $i = $yeniIlan();
    esit(1000, $i->dusenFiyat($t0), 'fiyat');
    esit(Durum::DUSUYOR, $i->durum($t0), 'durum');
});
kontrol('3 saat sonra fiyat 700', function () use ($yeniIlan, $t0) {
    $i = $yeniIlan();
    esit(700, $i->dusenFiyat($t0->modify('+3 hours 30 minutes')));
});
kontrol('rezerv tabanının (500) altına inmez', function () use ($yeniIlan, $t0) {
    $i = $yeniIlan();
    esit(500, $i->dusenFiyat($t0->modify('+50 hours')));
});

echo "İlk teklifle dönüşüm:\n";
kontrol('ilk teklif o anki düşmüş fiyatı taban alır ve açık artırmaya geçer', function () use ($yeniIlan, $t0) {
    $i = $yeniIlan();
    $an = $t0->modify('+3 hours'); // fiyat 700
    $i->teklifVer('ali', 700, $an);
    esit(Durum::ACIK_ARTIRMA, $i->durum($an), 'durum');
    esit(700, $i->guncelFiyat($an), 'güncel fiyat');
});
kontrol('düşmüş fiyatın altındaki ilk teklif reddedilir', function () use ($yeniIlan, $t0) {
    $i = $yeniIlan();
    $an = $t0->modify('+3 hours'); // fiyat 700
    firlatir(fn () => $i->teklifVer('ali', 699, $an));
});
kontrol('ilk tekliften 24 saat sonra kapanır', function () use ($yeniIlan, $t0) {
    $i = $yeniIlan();
    $an = $t0->modify('+3 hours');
    $i->teklifVer('ali', 700, $an);
    esit(Durum::ACIK_ARTIRMA, $i->durum($an->modify('+23 hours')));
    esit(Durum::KAPANDI, $i->durum($an->modify('+24 hours 1 second')));
});

echo "Açık artırma & artırım adımı:\n";
kontrol('artırım tablosu doğru', function () {
    esit(50, Ilan::artirimAdimi(700));
    esit(100, Ilan::artirimAdimi(1000));
    esit(250, Ilan::artirimAdimi(5000));
});
kontrol('adım altındaki teklif reddedilir, geçerli teklif kabul edilir', function () use ($yeniIlan, $t0) {
    $i = $yeniIlan();
    $an = $t0->modify('+3 hours');
    $i->teklifVer('ali', 700, $an); // 700, adım 50 -> min 750
    firlatir(fn () => $i->teklifVer('veli', 720, $an->modify('+1 hour')));
    $i->teklifVer('veli', 750, $an->modify('+1 hour'));
    esit(750, $i->guncelFiyat($an->modify('+1 hour')));
});

echo "Anti-snipe:\n";
kontrol('son 2 dk içindeki teklif sayacı uzatır', function () use ($yeniIlan, $t0) {
    $i = $yeniIlan();
    $an = $t0->modify('+3 hours');
    $i->teklifVer('ali', 700, $an);
    $eskiBitis = $i->bitisZamani();
    // Bitişe 1 dk kala teklif
    $sonAn = $eskiBitis->modify('-1 minute');
    $i->teklifVer('veli', 750, $sonAn);
    $yeniBitis = $i->bitisZamani();
    esit(true, $yeniBitis > $eskiBitis, 'sayaç uzamalı');
    esit(120, $yeniBitis->getTimestamp() - $sonAn->getTimestamp(), 'kalan 2 dk olmalı');
});

echo "\n{$basari}/{$gecen} test geçti.\n";
exit($basari === $gecen ? 0 : 1);
