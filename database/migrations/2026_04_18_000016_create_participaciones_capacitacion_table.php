<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('participaciones_capacitacion', function (Blueprint $table) {
            $table->id('cod_participaciones_capacitacion');
            $table->unsignedBigInteger('cod_personas_no_videntes_pca');
            $table->unsignedBigInteger('cod_capacitaciones_pca');
            $table->date('fei_pca');
            $table->enum('est_pca', ['INSCRITO','EN CURSO','APROBADO','REPROBADO','ABANDONÓ'])->default('INSCRITO');
            $table->text('obs_pca')->nullable();
            $table->timestamp('fcr_pca')->useCurrent();
            $table->timestamp('fmo_pca')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_pca')->nullable();

            $table->foreign('cod_personas_no_videntes_pca', 'fk_pca_pnv')
                ->references('cod_personas_no_videntes')
                ->on('personas_no_videntes');

            $table->foreign('cod_capacitaciones_pca', 'fk_pca_cap')
                ->references('cod_capacitaciones')
                ->on('capacitaciones');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participaciones_capacitacion');
    }
};