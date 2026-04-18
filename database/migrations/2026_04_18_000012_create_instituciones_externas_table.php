<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('instituciones_externas', function (Blueprint $table) {
            $table->id('cod_instituciones_externas');
            $table->string('nom_iex', 150)->unique();
            $table->enum('tip_iex', ['HOSPITAL','CLÍNICA','CENTRO DE SALUD','SEDE LEGAL','OFICINA ADMINISTRATIVA','OTRO']);
            $table->string('ciu_iex', 100)->nullable();
            $table->string('tel_iex', 20)->nullable();
            $table->string('cor_iex', 150)->nullable();
            $table->boolean('act_iex')->default(true);
            $table->timestamp('fcr_iex')->useCurrent();
            $table->timestamp('fmo_iex')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_iex')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instituciones_externas');
    }
};
