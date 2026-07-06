<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adresler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tur');                 // 'teslimat' | 'fatura'
            $table->string('ad_soyad');
            $table->string('telefon', 20)->nullable();
            $table->string('il')->nullable();
            $table->string('ilce')->nullable();
            $table->text('adres');
            $table->string('posta_kodu', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adresler');
    }
};
