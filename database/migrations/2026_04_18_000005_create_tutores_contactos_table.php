<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tutores_contactos', function (Blueprint $table) {
            $table->id('cod_tutores_contactos');
            $table->unsignedBigInteger('cod_personas_no_videntes_tco');
            $table->string('nom_tco', 100);
            $table->string('app_tco', 100);
            $table->string('apm_tco', 100);
            $table->string('ape_tco', 255);
            $table->string('par_tco', 50);
            $table->string('tel_tco', 20);
            $table->string('cor_tco', 150)->nullable();
            $table->boolean('etl_tco')->default(false);
            $table->timestamp('fcr_tco')->useCurrent();
            $table->timestamp('fmo_tco')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_tco')->nullable();

            $table->foreign('cod_personas_no_videntes_tco')->references('cod_personas_no_videntes')->on('personas_no_videntes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutores_contactos');
    }
};
