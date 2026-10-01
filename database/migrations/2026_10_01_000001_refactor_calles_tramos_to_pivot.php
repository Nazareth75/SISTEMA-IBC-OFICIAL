<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Crear la tabla pivote ruta_calles_tramos
        if (!Schema::hasTable('ruta_calles_tramos')) {
            Schema::create('ruta_calles_tramos', function (Blueprint $table) {
                $table->id('cod_ruta_calles_tramos');
                $table->unsignedBigInteger('cod_rutas_rct');
                $table->unsignedBigInteger('cod_calles_tramos_rct');
                $table->integer('ord_rct')->default(1);
                $table->timestamp('fcr_rct')->useCurrent();
                $table->timestamp('fmo_rct')->useCurrent()->useCurrentOnUpdate();
                $table->timestamp('fbj_rct')->nullable();

                $table->foreign('cod_rutas_rct')
                    ->references('cod_rutas')
                    ->on('rutas')
                    ->onDelete('cascade');

                $table->foreign('cod_calles_tramos_rct')
                    ->references('cod_calles_tramos')
                    ->on('calles_tramos')
                    ->onDelete('cascade');
            });
        }

        // 2. Si existen tramos legacy en calles_tramos con cod_rutas_ctr, migrar asociaciones a la pivote
        if (Schema::hasColumn('calles_tramos', 'cod_rutas_ctr')) {
            $tramosExistentes = DB::table('calles_tramos')
                ->whereNotNull('cod_rutas_ctr')
                ->get();

            foreach ($tramosExistentes as $t) {
                $existe = DB::table('ruta_calles_tramos')
                    ->where('cod_rutas_rct', $t->cod_rutas_ctr)
                    ->where('cod_calles_tramos_rct', $t->cod_calles_tramos)
                    ->exists();

                if (!$existe) {
                    // Evitar duplicar orden si ya existe
                    $ordExistente = DB::table('ruta_calles_tramos')
                        ->where('cod_rutas_rct', $t->cod_rutas_ctr)
                        ->where('ord_rct', $t->ord_ctr ?? 1)
                        ->exists();

                    $ordenFinal = $ordExistente ? (DB::table('ruta_calles_tramos')->where('cod_rutas_rct', $t->cod_rutas_ctr)->max('ord_rct') + 1) : ($t->ord_ctr ?? 1);

                    DB::table('ruta_calles_tramos')->insert([
                        'cod_rutas_rct' => $t->cod_rutas_ctr,
                        'cod_calles_tramos_rct' => $t->cod_calles_tramos,
                        'ord_rct' => $ordenFinal,
                        'fcr_rct' => $t->fcr_ctr ?? now(),
                        'fmo_rct' => $t->fmo_ctr ?? now(),
                    ]);
                }
            }
        }

        // 3. Modificar la tabla calles_tramos
        Schema::table('calles_tramos', function (Blueprint $table) {
            if (!Schema::hasColumn('calles_tramos', 'geo_ctr')) {
                $table->json('geo_ctr')->nullable()->after('lon_ctr');
            }

            try {
                $table->dropForeign(['cod_rutas_ctr']);
            } catch (\Throwable $e) {
                // Ignorar si no existía o ya fue soltada
            }
        });

        DB::statement("ALTER TABLE calles_tramos MODIFY cod_rutas_ctr BIGINT UNSIGNED NULL, MODIFY ord_ctr INT NULL");
    }

    public function down(): void
    {
        // Protección contra pérdida de datos en rollback: si existen tramos compartidos entre múltiples rutas
        $tramosCompartidos = DB::table('ruta_calles_tramos')
            ->select('cod_calles_tramos_rct')
            ->whereNull('fbj_rct')
            ->groupBy('cod_calles_tramos_rct')
            ->havingRaw('COUNT(DISTINCT cod_rutas_rct) > 1')
            ->count();

        if ($tramosCompartidos > 0) {
            throw new \RuntimeException(
                "ROLLBACK CANCELADO: Existen tramos geográficos asociados a múltiples rutas en relación N:M. " .
                "Revertir la migración provocaría pérdida silenciosa e irreparable de asociaciones entre rutas y tramos. " .
                "Desvincule manualmente los tramos compartidos antes de ejecutar el rollback."
            );
        }

        // Restaurar asociaciones únicas legacy a calles_tramos
        $asociaciones = DB::table('ruta_calles_tramos')->whereNull('fbj_rct')->get();
        foreach ($asociaciones as $assoc) {
            DB::table('calles_tramos')
                ->where('cod_calles_tramos', $assoc->cod_calles_tramos_rct)
                ->update([
                    'cod_rutas_ctr' => $assoc->cod_rutas_rct,
                    'ord_ctr'       => $assoc->ord_rct,
                ]);
        }

        Schema::table('calles_tramos', function (Blueprint $table) {
            if (Schema::hasColumn('calles_tramos', 'geo_ctr')) {
                $table->dropColumn('geo_ctr');
            }
        });

        Schema::dropIfExists('ruta_calles_tramos');
    }
};
