<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rutas', function (Blueprint $table) {
            $table->id('cod_rutas');
            $table->string('ide_rut', 20)->unique();
            $table->string('nom_rut', 150);
            $table->string('des_rut', 300)->nullable();
            $table->string('ori_rut', 200);
            $table->string('dst_rut', 200);
            $table->decimal('dis_rut', 10, 2)->nullable();
            $table->integer('tim_rut')->nullable();
            $table->enum('est_rut', ['BORRADOR','EN REVISIÓN','APROBADA','PUBLICADA','DESACTUALIZADA','ARCHIVADA'])->default('BORRADOR');
            $table->unsignedBigInteger('cod_usuarios_rut_cre')->nullable();
            $table->unsignedBigInteger('cod_usuarios_rut_apr')->nullable();
            $table->timestamp('fap_rut')->nullable();
            $table->text('obs_rut')->nullable();
            $table->timestamp('fcr_rut')->useCurrent();
            $table->timestamp('fmo_rut')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_rut')->nullable();

            $table->foreign('cod_usuarios_rut_cre')->references('cod_usuarios')->on('usuarios');
            $table->foreign('cod_usuarios_rut_apr')->references('cod_usuarios')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutas');
    }
};
