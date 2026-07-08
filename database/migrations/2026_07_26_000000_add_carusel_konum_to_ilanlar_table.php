<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->string('carusel_konum', 20)->default('sol-alt')->after('carusel_sira');
        });
    }

    public function down(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->dropColumn('carusel_konum');
        });
    }
};
