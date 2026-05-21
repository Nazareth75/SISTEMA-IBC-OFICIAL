<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reportes_obstaculos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('tipo_obstaculo');
            $table->text('descripcion');
            $table->string('direccion_referencia')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->string('foto')->nullable();
            $table->string('nivel_riesgo');
            $table->integer('prioridad')->default(0);
            $table->string('estado')->default('REPORTADO');
            $table->unsignedBigInteger('reportado_por')->nullable();
            $table->unsignedBigInteger('validado_por')->nullable();
            $table->timestamp('fecha_reporte')->nullable();
            $table->timestamp('fecha_validacion')->nullable();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->text('observacion_validacion')->nullable();
            $table->timestamps();

            $table->foreign('reportado_por')->references('cod_usuarios')->on('usuarios');
            $table->foreign('validado_por')->references('cod_usuarios')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes_obstaculos');
    }
};
