<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asignacion_beneficios', function (Blueprint $table) {
            $table->id('cod_asignacion_beneficios');
            $table->unsignedBigInteger('cod_afiliaciones_abe');
            $table->unsignedBigInteger('cod_beneficios_sociales_abe');
            $table->date('fas_abe');
            $table->date('ffi_abe')->nullable();
            $table->enum('est_abe', ['ACTIVO','SUSPENDIDO','FINALIZADO'])->default('ACTIVO');
            $table->text('obs_abe')->nullable();
            $table->unsignedBigInteger('cod_usuarios_abe')->nullable();
            $table->timestamp('fcr_abe')->useCurrent();
            $table->timestamp('fmo_abe')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_abe')->nullable();

            $table->foreign('cod_afiliaciones_abe')->references('cod_afiliaciones')->on('afiliaciones');
            $table->foreign('cod_beneficios_sociales_abe')->references('cod_beneficios_sociales')->on('beneficios_sociales');
            $table->foreign('cod_usuarios_abe')->references('cod_usuarios')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_beneficios');
    }
};
