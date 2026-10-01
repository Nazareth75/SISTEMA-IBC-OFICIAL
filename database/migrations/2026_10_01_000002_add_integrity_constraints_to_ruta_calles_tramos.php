<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('ruta_calles_tramos')) {
            return;
        }

        $indexes = DB::select("
            SELECT INDEX_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'ruta_calles_tramos'
        ");

        $indexNames = array_column($indexes, 'INDEX_NAME');

        Schema::table('ruta_calles_tramos', function (Blueprint $table) use ($indexNames) {
            if (!in_array('uq_ruta_tramo', $indexNames)) {
                $table->unique(['cod_rutas_rct', 'cod_calles_tramos_rct'], 'uq_ruta_tramo');
            }

            if (!in_array('uq_ruta_orden', $indexNames)) {
                $table->unique(['cod_rutas_rct', 'ord_rct'], 'uq_ruta_orden');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ruta_calles_tramos')) {
            return;
        }

        $indexes = DB::select("
            SELECT INDEX_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'ruta_calles_tramos'
        ");

        $indexNames = array_column($indexes, 'INDEX_NAME');

        Schema::table('ruta_calles_tramos', function (Blueprint $table) use ($indexNames) {
            if (in_array('uq_ruta_tramo', $indexNames)) {
                $table->dropUnique('uq_ruta_tramo');
            }

            if (in_array('uq_ruta_orden', $indexNames)) {
                $table->dropUnique('uq_ruta_orden');
            }
        });
    }
};
