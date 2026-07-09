<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Ana sayfa altındaki 4 ürün kartı için lot seçimi. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->boolean('kart')->default(false)->after('coverflow_sira');
            $table->unsignedInteger('kart_sira')->nullable()->after('kart');
        });
    }

    public function down(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->dropColumn(['kart', 'kart_sira']);
        });
    }
};
