<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->unsignedBigInteger('lider_id')->nullable(); // önde olan kullanıcı (proxy teklif)
            $table->unsignedInteger('lider_max')->nullable();   // liderin gizli maksimum teklifi
        });
    }

    public function down(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->dropColumn(['lider_id', 'lider_max']);
        });
    }
};
