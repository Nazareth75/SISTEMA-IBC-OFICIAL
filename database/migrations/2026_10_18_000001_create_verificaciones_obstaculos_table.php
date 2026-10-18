<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('verificaciones_obstaculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cod_obstaculo');
            $table->unsignedBigInteger('cod_usuario');
            $table->enum('respuesta', ['SIGUE_PRESENTE', 'YA_NO_ESTA', 'NO_PUEDE_VERIFICAR']);
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->float('precision_gps')->nullable();
            $table->string('foto', 255)->nullable();
            $table->string('observacion', 500)->nullable();
            $table->enum('canal', ['WEB', 'APP', 'BASTON_ASISTIDO'])->default('WEB');
            $table->timestamps();

            $table->foreign('cod_obstaculo')
                ->references('cod_obstaculos')
                ->on('obstaculos')
                ->onDelete('cascade');

            $table->foreign('cod_usuario')
                ->references('cod_usuarios')
                ->on('usuarios');

            // Índices para acelerar el cálculo de consenso por obstáculo y voluntario
            $table->index(['cod_obstaculo', 'cod_usuario', 'created_at'], 'idx_vob_obs_usu_created');
            $table->index(['cod_obstaculo', 'respuesta'], 'idx_vob_obs_resp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verificaciones_obstaculos');
    }
};
