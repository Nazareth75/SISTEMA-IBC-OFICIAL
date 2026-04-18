<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accesos_salud', function (Blueprint $table) {
            $table->id('cod_accesos_salud');
            $table->unsignedBigInteger('cod_afiliaciones_asa');
            $table->unsignedBigInteger('cod_instituciones_externas_asa');
            $table->date('fea_asa');
            $table->string('mot_asa', 250)->nullable();
            $table->enum('est_asa', ['ACTIVO','FINALIZADO','SUSPENDIDO'])->default('ACTIVO');
            $table->text('obs_asa')->nullable();
            $table->unsignedBigInteger('cod_usuarios_asa')->nullable();
            $table->timestamp('fcr_asa')->useCurrent();
            $table->timestamp('fmo_asa')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_asa')->nullable();

            $table->foreign('cod_afiliaciones_asa')->references('cod_afiliaciones')->on('afiliaciones');
            $table->foreign('cod_instituciones_externas_asa')->references('cod_instituciones_externas')->on('instituciones_externas');
            $table->foreign('cod_usuarios_asa')->references('cod_usuarios')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accesos_salud');
    }
};
