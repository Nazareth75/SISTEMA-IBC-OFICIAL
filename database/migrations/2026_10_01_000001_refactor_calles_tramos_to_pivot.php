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
                // Verificar que no se haya insertado previamente
                $existe = DB::table('ruta_calles_tramos')
                    ->where('cod_rutas_rct', $t->cod_rutas_ctr)
                    ->where('cod_calles_tramos_rct', $t->cod_calles_tramos)
                    ->exists();

                if (!$existe) {
                    DB::table('ruta_calles_tramos')->insert([
                        'cod_rutas_rct' => $t->cod_rutas_ctr,
                        'cod_calles_tramos_rct' => $t->cod_calles_tramos,
                        'ord_rct' => $t->ord_ctr ?? 1,
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
        Schema::table('calles_tramos', function (Blueprint $table) {
            if (Schema::hasColumn('calles_tramos', 'geo_ctr')) {
                $table->dropColumn('geo_ctr');
            }
            $table->unsignedBigInteger('cod_rutas_ctr')->nullable(false)->change();
            $table->integer('ord_ctr')->nullable(false)->change();
            $table->foreign('cod_rutas_ctr')->references('cod_rutas')->on('rutas');
        });

        Schema::dropIfExists('ruta_calles_tramos');
    }
};
