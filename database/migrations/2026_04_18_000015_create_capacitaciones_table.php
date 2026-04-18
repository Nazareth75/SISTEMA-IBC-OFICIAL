<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('capacitaciones', function (Blueprint $table) {
            $table->id('cod_capacitaciones');
            $table->string('ide_cap', 20)->unique();
            $table->string('nom_cap', 150);
            $table->string('obj_cap', 300)->nullable();
            $table->unsignedBigInteger('cod_tipos_capacitacion_cap');
            $table->date('fei_cap')->nullable();
            $table->date('fef_cap')->nullable();
            $table->string('lug_cap', 200)->nullable();
            $table->unsignedBigInteger('cod_usuarios_cap')->nullable();
            $table->enum('est_cap', ['PLANIFICADA','EN CURSO','FINALIZADA','CANCELADA'])->default('PLANIFICADA');
            $table->timestamp('fcr_cap')->useCurrent();
            $table->timestamp('fmo_cap')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_cap')->nullable();

            $table->foreign('cod_tipos_capacitacion_cap')->references('cod_tipos_capacitacion')->on('tipos_capacitacion');
            $table->foreign('cod_usuarios_cap')->references('cod_usuarios')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacitaciones');
    }
};
