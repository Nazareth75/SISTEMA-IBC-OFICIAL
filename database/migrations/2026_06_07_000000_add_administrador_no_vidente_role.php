<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles')->insertOrIgnore([
            'nom_rol' => 'ADMINISTRADOR_NO_VIDENTE',
            'des_rol' => 'Administrador con discapacidad visual que utiliza interfaz auditiva y comandos de voz para operar el sistema.',
            'act_rol' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')
            ->where('nom_rol', 'ADMINISTRADOR_NO_VIDENTE')
            ->delete();
    }
};
