<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('obstaculos', function (Blueprint $table) {
            $table->id('cod_obstaculos');
            $table->unsignedBigInteger('cod_calles_tramos_obs');
            $table->enum('tip_obs', ['POSTE','ESCALÓN','OBRA','VEHÍCULO','BACHE','VENDEDOR','OTRO']);
            $table->string('des_obs', 250);
            $table->enum('est_obs', ['REPORTADO','ACTIVO','RESUELTO'])->default('REPORTADO');
            $table->enum('ori_obs', ['TECNICO','VOLUNTARIO','AFILIADO'])->default('TECNICO');
            $table->unsignedBigInteger('cod_usuarios_obs')->nullable();
            $table->string('fot_obs', 255)->nullable();
            $table->timestamp('fcr_obs')->useCurrent();
            $table->timestamp('fmo_obs')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_obs')->nullable();

            $table->foreign('cod_calles_tramos_obs')->references('cod_calles_tramos')->on('calles_tramos');
            $table->foreign('cod_usuarios_obs')->references('cod_usuarios')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obstaculos');
    }
};
