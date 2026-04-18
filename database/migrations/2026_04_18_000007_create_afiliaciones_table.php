<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('afiliaciones', function (Blueprint $table) {
            $table->id('cod_afiliaciones');
            $table->string('ide_afi', 20)->unique();
            $table->unsignedBigInteger('cod_personas_no_videntes_afi');
            $table->unsignedBigInteger('cod_tipos_afiliado_afi');
            $table->date('fia_afi');
            $table->date('fve_afi')->nullable();
            $table->enum('est_afi', ['ACTIVA','INACTIVA','SUSPENDIDA','VENCIDA'])->default('ACTIVA');
            $table->text('obs_afi')->nullable();
            $table->unsignedBigInteger('cod_usuarios_afi')->nullable();
            $table->timestamp('fcr_afi')->useCurrent();
            $table->timestamp('fmo_afi')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_afi')->nullable();

            $table->foreign('cod_personas_no_videntes_afi')->references('cod_personas_no_videntes')->on('personas_no_videntes');
            $table->foreign('cod_tipos_afiliado_afi')->references('cod_tipos_afiliado')->on('tipos_afiliado');
            $table->foreign('cod_usuarios_afi')->references('cod_usuarios')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('afiliaciones');
    }
};
