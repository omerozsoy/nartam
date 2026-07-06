<?php

use App\Models\Ilan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->unsignedInteger('lot_no')->nullable()->after('baslik');
        });

        // Mevcut açık artırmadaki (teklif almış) ilanlara sıra numarası ver.
        $no = 0;
        Ilan::whereNotNull('ilk_teklif_zamani')
            ->orderBy('ilk_teklif_zamani')
            ->get()
            ->each(fn (Ilan $i) => $i->update(['lot_no' => ++$no]));
    }

    public function down(): void
    {
        Schema::table('ilanlar', function (Blueprint $table) {
            $table->dropColumn('lot_no');
        });
    }
};
