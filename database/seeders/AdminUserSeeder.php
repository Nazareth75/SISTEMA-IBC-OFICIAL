<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::query()->where('nom_rol', 'ADMINISTRADOR')->first();

        if (!$adminRole) {
            $adminRole = Role::query()->create([
                'nom_rol' => 'ADMINISTRADOR',
                'des_rol' => 'Acceso total al sistema. Puede gestionar usuarios, parámetros y todos los módulos.',
                'act_rol' => true,
            ]);
        }

        $email = 'admin@ibc.bo';
        $password = 'password';

        $admin = User::query()->where('cor_usu', $email)->first();

        if (!$admin) {
            $admin = new User();
            $admin->ide_usu = 'USR-001';
            $admin->cor_usu = $email;
        }

        $admin->fill([
            'nom_usu' => 'ADMINISTRADOR',
            'ape_usu' => 'SISTEMA',
            'cii_usu' => '00000000',
            'con_usu' => bcrypt($password),
            'cod_roles_usu' => $adminRole->getKey(),
            'est_usu' => 'ACTIVO',
        ]);

        $admin->save();
    }
}
