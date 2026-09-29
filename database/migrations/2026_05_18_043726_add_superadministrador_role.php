<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->insertOrIgnore([
            'nom_rol' => 'SUPERADMINISTRADOR',
            'des_rol' => 'Rol principal del sistema. No puede ser eliminado ni suspendido.',
            'act_rol' => true,
        ]);

        $superadmin = DB::table('roles')
            ->where('nom_rol', 'SUPERADMINISTRADOR')
            ->first();

        if ($superadmin) {
            DB::table('usuarios')
                ->where('cor_usu', 'admin@ibc.bo')
                ->update([
                    'cod_roles_usu' => $superadmin->cod_roles,
                ]);
        }
    }

    public function down(): void
    {
        $admin = DB::table('roles')
            ->where('nom_rol', 'ADMINISTRADOR')
            ->first();

        if ($admin) {
            DB::table('usuarios')
                ->where('cor_usu', 'admin@ibc.bo')
                ->update([
                    'cod_roles_usu' => $admin->cod_roles,
                ]);
        }

        DB::table('roles')
            ->where('nom_rol', 'SUPERADMINISTRADOR')
            ->delete();
    }
};
