<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muzayedeler', function (Blueprint $table) {
            $table->id();
            $table->string('no');                       // Müzayede numarası (örn. 407)
            $table->string('ad');                       // Müzayede ismi
            $table->dateTime('baslangic');              // Teklifler bu an açılır
            $table->dateTime('bitis');                  // İlk lotun kapanış anı (sonrakiler kademeli)
            $table->unsignedInteger('esik_lot')->default(10);   // İlk kaç lot birinci aralıkla
            $table->unsignedInteger('aralik1')->default(2);     // Birinci aralık (dk)
            $table->unsignedInteger('aralik2')->default(1);     // Sonraki aralık (dk)
            $table->boolean('aktif')->default(false);   // Sitede gösterilen müzayede
            $table->timestamps();
        });

        Schema::table('ilanlar', function (Blueprint $table) {
            $table->foreignId('muzayede_id')->nullable()->after('id')
                ->constrained('muzayedeler')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('muzayede_id');
        });
        Schema::dropIfExists('muzayedeler');
    }
};
