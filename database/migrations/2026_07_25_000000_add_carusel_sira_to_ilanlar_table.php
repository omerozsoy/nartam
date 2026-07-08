<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->unsignedInteger('carusel_sira')->nullable()->after('carusel');
        });
    }

    public function down(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->dropColumn('carusel_sira');
        });
    }
};
