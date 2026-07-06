<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teklifler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ilan_id')->constrained('ilanlar')->cascadeOnDelete();
            $table->foreignId('kullanici_id')->constrained('users');
            $table->unsignedInteger('miktar');
            $table->dateTime('zaman');
            $table->timestamps();

            $table->index('ilan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teklifler');
    }
};
