<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluaciones_riesgo', function (Blueprint $table) {
            $table->id('cod_evaluaciones_riesgo');
            $table->unsignedBigInteger('cod_calles_tramos_eri');
            $table->unsignedBigInteger('cod_niveles_seguridad_eri');
            $table->string('met_eri', 150)->nullable();
            $table->text('obs_eri')->nullable();
            $table->unsignedBigInteger('cod_usuarios_eri')->nullable();
            $table->date('fee_eri');
            $table->timestamp('fcr_eri')->useCurrent();
            $table->timestamp('fmo_eri')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_eri')->nullable();

            $table->foreign('cod_calles_tramos_eri')->references('cod_calles_tramos')->on('calles_tramos');
            $table->foreign('cod_niveles_seguridad_eri')->references('cod_niveles_seguridad')->on('niveles_seguridad');
            $table->foreign('cod_usuarios_eri')->references('cod_usuarios')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_riesgo');
    }
};
