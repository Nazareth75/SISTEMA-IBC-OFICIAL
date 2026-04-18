<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calles_tramos', function (Blueprint $table) {
            $table->id('cod_calles_tramos');
            $table->unsignedBigInteger('cod_rutas_ctr');
            $table->string('nom_ctr', 150);
            $table->enum('tip_ctr', ['ACERA','CRUCE','PASO PEATONAL','CALLE','ESCALERA','RAMPA','OTRO']);
            $table->integer('ord_ctr')->default(1);
            $table->decimal('lon_ctr', 10, 2)->nullable();
            $table->boolean('act_ctr')->default(true);
            $table->timestamp('fcr_ctr')->useCurrent();
            $table->timestamp('fmo_ctr')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_ctr')->nullable();

            $table->foreign('cod_rutas_ctr')->references('cod_rutas')->on('rutas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calles_tramos');
    }
};
