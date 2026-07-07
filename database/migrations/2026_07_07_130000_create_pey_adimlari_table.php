<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pey_adimlari', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('alt_sinir')->unique(); // bu fiyattan itibaren
            $table->unsignedInteger('adim');                // artırım tutarı (₺)
            $table->timestamps();
        });

        // Varsayılan kademeler (yönetim panelinden değiştirilebilir)
        DB::table('pey_adimlari')->insert([
            ['alt_sinir' => 0, 'adim' => 50, 'created_at' => now(), 'updated_at' => now()],
            ['alt_sinir' => 1000, 'adim' => 100, 'created_at' => now(), 'updated_at' => now()],
            ['alt_sinir' => 5000, 'adim' => 250, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pey_adimlari');
    }
};
