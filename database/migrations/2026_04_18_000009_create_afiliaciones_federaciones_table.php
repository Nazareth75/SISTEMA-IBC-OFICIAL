<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('afiliaciones_federaciones', function (Blueprint $table) {
            $table->id('cod_afiliaciones_federaciones');
            $table->unsignedBigInteger('cod_afiliaciones_afe');
            $table->unsignedBigInteger('cod_federaciones_afe');
            $table->date('ffi_afe');
            $table->date('fff_afe')->nullable();
            $table->enum('est_afe', ['ACTIVA','INACTIVA','VENCIDA'])->default('ACTIVA');
            $table->text('obs_afe')->nullable();
            $table->timestamp('fcr_afe')->useCurrent();
            $table->timestamp('fmo_afe')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_afe')->nullable();

            $table->foreign('cod_afiliaciones_afe')->references('cod_afiliaciones')->on('afiliaciones');
            $table->foreign('cod_federaciones_afe')->references('cod_federaciones')->on('federaciones');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('afiliaciones_federaciones');
    }
};
