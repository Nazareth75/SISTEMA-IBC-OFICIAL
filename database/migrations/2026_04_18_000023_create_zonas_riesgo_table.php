<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('zonas_riesgo', function (Blueprint $table) {
            $table->id('cod_zonas_riesgo');
            $table->string('nom_zri', 150);
            $table->unsignedBigInteger('cod_calles_tramos_zri');
            $table->unsignedBigInteger('cod_niveles_seguridad_zri');
            $table->text('des_zri')->nullable();
            $table->boolean('act_zri')->default(true);
            $table->timestamp('fcr_zri')->useCurrent();
            $table->timestamp('fmo_zri')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_zri')->nullable();

            $table->foreign('cod_calles_tramos_zri')->references('cod_calles_tramos')->on('calles_tramos');
            $table->foreign('cod_niveles_seguridad_zri')->references('cod_niveles_seguridad')->on('niveles_seguridad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zonas_riesgo');
    }
};
