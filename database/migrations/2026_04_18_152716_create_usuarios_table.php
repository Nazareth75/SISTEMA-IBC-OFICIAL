<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('cod_usuarios');
            $table->string('ide_usu', 20)->unique();
            $table->string('nom_usu', 100);
            $table->string('ape_usu', 100);
            $table->string('cii_usu', 20)->unique();
            $table->string('cor_usu', 150)->unique();
            $table->string('con_usu', 255);
            $table->string('tel_usu', 20)->nullable();
            $table->string('fot_usu', 255)->nullable();
            $table->unsignedBigInteger('cod_roles_usu');
            $table->enum('est_usu', ['ACTIVO','INACTIVO','SUSPENDIDO'])->default('ACTIVO');
            $table->string('tok_usu', 100)->nullable();
            $table->timestamp('uac_usu')->nullable();
            $table->timestamp('fcr_usu')->useCurrent();
            $table->timestamp('fmo_usu')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_usu')->nullable();

            $table->foreign('cod_roles_usu')->references('cod_roles')->on('roles');
        });

        DB::table('usuarios')->insert([
            'ide_usu' => 'USR-001',
            'nom_usu' => 'ADMINISTRADOR',
            'ape_usu' => 'SISTEMA',
            'cii_usu' => '00000000',
            'cor_usu' => 'admin@ibc.bo',
            'con_usu' => bcrypt('password'),
            'cod_roles_usu' => 1,
            'est_usu' => 'ACTIVO',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
