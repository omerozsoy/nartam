<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Yeni müzayede modeli: fiyat baştan düşmez, "ilk teklif 24 saat başlatır" kuralı kalkar.
 * Eski düşüş alanları artık kullanılmıyor; yeni içe aktarımların bunları doldurması
 * gerekmesin diye nullable yapılır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->unsignedInteger('saatlik_dusus')->nullable()->change();
            $table->dateTime('baslangic_zamani')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->unsignedInteger('saatlik_dusus')->nullable(false)->change();
            $table->dateTime('baslangic_zamani')->nullable(false)->change();
        });
    }
};
