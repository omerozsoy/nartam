<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pey_adimlari', function (Blueprint $table) {
            $table->unsignedInteger('ust_sinir')->nullable()->after('alt_sinir'); // bitiş (boş = ve üzeri)
        });

        // Mevcut kademeleri doldur: üst = bir sonraki kademenin başlangıcı - 1
        $rows = DB::table('pey_adimlari')->orderBy('alt_sinir')->get()->all();
        foreach ($rows as $i => $r) {
            $ust = isset($rows[$i + 1]) ? $rows[$i + 1]->alt_sinir - 1 : null;
            DB::table('pey_adimlari')->where('id', $r->id)->update(['ust_sinir' => $ust]);
        }
    }

    public function down(): void
    {
        Schema::table('pey_adimlari', function (Blueprint $table) {
            $table->dropColumn('ust_sinir');
        });
    }
};
