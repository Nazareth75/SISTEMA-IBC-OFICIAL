<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recorridos_realizados', function (Blueprint $table) {
            $table->id('cod_recorridos_realizados');
            $table->unsignedBigInteger('cod_personas_no_videntes_rre');
            $table->unsignedBigInteger('cod_afiliaciones_rre');
            $table->unsignedBigInteger('cod_rutas_rre');
            $table->date('fer_rre');
            $table->time('hoi_rre')->nullable();
            $table->time('hof_rre')->nullable();
            $table->enum('est_rre', ['EN CURSO','COMPLETADO','INTERRUMPIDO'])->default('EN CURSO');
            $table->unsignedBigInteger('cod_usuarios_rre')->nullable();
            $table->text('obs_rre')->nullable();
            $table->timestamp('fcr_rre')->useCurrent();
            $table->timestamp('fmo_rre')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_rre')->nullable();

            $table->foreign('cod_personas_no_videntes_rre')->references('cod_personas_no_videntes')->on('personas_no_videntes');
            $table->foreign('cod_afiliaciones_rre')->references('cod_afiliaciones')->on('afiliaciones');
            $table->foreign('cod_rutas_rre')->references('cod_rutas')->on('rutas');
            $table->foreign('cod_usuarios_rre')->references('cod_usuarios')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recorridos_realizados');
    }
};
