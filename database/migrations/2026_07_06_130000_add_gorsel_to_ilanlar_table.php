<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->string('gorsel_url')->nullable()->after('baslik');
            $table->string('alt_baslik')->nullable()->after('gorsel_url'); // eser/kısa açıklama satırı
            $table->text('aciklama')->nullable()->after('alt_baslik');      // detay sayfası metni
        });
    }

    public function down(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->dropColumn(['gorsel_url', 'alt_baslik', 'aciklama']);
        });
    }
};
