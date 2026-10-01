<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Esta migración actúa como un guardián de integridad idempotente
     * para asegurar la presencia de las restricciones UNIQUE uq_ruta_tramo y uq_ruta_orden.
     */
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

    /**
     * Reversión de la migración:
     * Al ser una migración de garantía de integridad, en entornos donde los índices existían previamente,
     * la reversión no debe destruir las restricciones únicas preexistentes de la base de datos.
     */
    public function down(): void
    {
        // Se preservan los índices de la tabla pivote para evitar la destrucción accidental de restricciones preexistentes.
    }
};
