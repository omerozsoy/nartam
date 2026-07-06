<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            // Düşüş periyodu (saniye): 1=saniye, 60=dakika, 3600=saat. Varsayılan saat.
            $table->unsignedInteger('dusus_periyodu')->default(3600)->after('saatlik_dusus');
        });
    }

    public function down(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->dropColumn('dusus_periyodu');
        });
    }
};
