<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tramites_instituciones', function (Blueprint $table) {
            $table->id('cod_tramites_instituciones');
            $table->unsignedBigInteger('cod_afiliaciones_tin');
            $table->unsignedBigInteger('cod_instituciones_externas_tin');
            $table->enum('tti_tin', ['SALUD','BENEFICIO SOCIAL','CAPACITACIÓN','LEGAL','ADMINISTRATIVO','OTRO']);
            $table->string('des_tin', 300);
            $table->date('fei_tin');
            $table->date('fer_tin')->nullable();
            $table->enum('est_tin', ['PENDIENTE','EN PROCESO','RESUELTO','RECHAZADO','ABANDONADO'])->default('PENDIENTE');
            $table->text('obs_tin')->nullable();
            $table->unsignedBigInteger('cod_usuarios_tin')->nullable();
            $table->timestamp('fcr_tin')->useCurrent();
            $table->timestamp('fmo_tin')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_tin')->nullable();

            $table->foreign('cod_afiliaciones_tin')->references('cod_afiliaciones')->on('afiliaciones');
            $table->foreign('cod_instituciones_externas_tin')->references('cod_instituciones_externas')->on('instituciones_externas');
            $table->foreign('cod_usuarios_tin')->references('cod_usuarios')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tramites_instituciones');
    }
};
