<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alttaki carusel (coverflow) için lot seçimi. Ust "Slider" (hero) 'carusel' alanini kullanir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->boolean('coverflow')->default(false)->after('carusel_arka');
            $table->unsignedInteger('coverflow_sira')->nullable()->after('coverflow');
        });
    }

    public function down(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->dropColumn(['coverflow', 'coverflow_sira']);
        });
    }
};
