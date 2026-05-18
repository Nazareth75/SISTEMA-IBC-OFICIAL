<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id('cod_roles');
            $table->string('nom_rol', 50)->unique();
            $table->string('des_rol', 200)->nullable();
            $table->boolean('act_rol')->default(true);
            $table->timestamp('fcr_rol')->useCurrent();
            $table->timestamp('fmo_rol')->useCurrent()->useCurrentOnUpdate();
        });

        DB::table('roles')->insert([
            ['nom_rol' => 'SUPERADMINISTRADOR', 'des_rol' => 'Rol principal del sistema. No puede ser eliminado ni suspendido.'],
            ['nom_rol' => 'ADMINISTRADOR', 'des_rol' => 'Acceso total al sistema. Puede gestionar usuarios, parámetros y todos los módulos.'],
            ['nom_rol' => 'TECNICO', 'des_rol' => 'Responsable de mapear rutas, tramos, obstáculos y evaluaciones de riesgo.'],
            ['nom_rol' => 'VOLUNTARIO', 'des_rol' => 'Acompaña recorridos, reporta obstáculos y da seguimiento a afiliados.'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
