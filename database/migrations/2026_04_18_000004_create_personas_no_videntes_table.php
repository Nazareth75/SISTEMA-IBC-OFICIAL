<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personas_no_videntes', function (Blueprint $table) {
            $table->id('cod_personas_no_videntes');
            $table->string('ide_pnv', 20)->unique();
            $table->string('nom_pnv', 100);
            $table->string('app_pnv', 100);
            $table->string('apm_pnv', 100);
            $table->string('ape_pnv', 255);
            $table->string('cii_pnv', 20)->unique();
            $table->string('ext_pnv', 10)->nullable();
            $table->date('fen_pnv');
            $table->enum('gen_pnv', ['MASCULINO','FEMENINO','OTRO']);
            $table->string('tel_pnv', 20)->nullable();
            $table->string('cor_pnv', 150)->nullable();
            $table->string('dir_pnv', 250)->nullable();
            $table->string('ciu_pnv', 100)->default('LA PAZ');
            $table->enum('gdi_pnv', ['TOTAL','PARCIAL','BAJA VISIÓN']);
            $table->string('cau_pnv', 150)->nullable();
            $table->enum('aut_pnv', ['INDEPENDIENTE','SEMI-DEPENDIENTE','DEPENDIENTE'])->default('SEMI-DEPENDIENTE');
            $table->string('fot_pnv', 255)->nullable();
            $table->enum('est_pnv', ['PENDIENTE','ACTIVO','INACTIVO','SUSPENDIDO','EGRESADO'])->default('PENDIENTE');
            $table->text('obs_pnv')->nullable();
            $table->unsignedBigInteger('cod_usuarios_pnv')->nullable();
            $table->timestamp('fcr_pnv')->useCurrent();
            $table->timestamp('fmo_pnv')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_pnv')->nullable();

            $table->foreign('cod_usuarios_pnv')->references('cod_usuarios')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas_no_videntes');
    }
};
