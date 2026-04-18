<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('condiciones_entorno', function (Blueprint $table) {
            $table->id('cod_condiciones_entorno');
            $table->unsignedBigInteger('cod_calles_tramos_cen');
            $table->enum('tip_cen', ['PAVIMENTO','ILUMINACIÓN','SEÑALÉTICA','ANCHO VEREDA','PENDIENTE','OTRO']);
            $table->string('des_cen', 250);
            $table->enum('niv_cen', ['BUENO','REGULAR','MALO'])->default('REGULAR');
            $table->timestamp('fcr_cen')->useCurrent();
            $table->timestamp('fmo_cen')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_cen')->nullable();

            $table->foreign('cod_calles_tramos_cen')->references('cod_calles_tramos')->on('calles_tramos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condiciones_entorno');
    }
};
