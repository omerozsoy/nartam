<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ilanlar', function (Blueprint $table) {
            $table->id();
            $table->string('baslik');
            $table->unsignedInteger('baslangic_fiyati');
            $table->unsignedInteger('saatlik_dusus');
            $table->unsignedInteger('rezerv_fiyat');
            $table->dateTime('baslangic_zamani');
            $table->dateTime('ilk_teklif_zamani')->nullable(); // null: hâlâ düşüş fazında
            $table->dateTime('bitis_zamani')->nullable();       // açık artırma bitişi
            $table->unsignedInteger('guncel_teklif')->nullable();
            $table->string('son_teklif_sahibi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ilanlar');
    }
};
