<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nom_rol' => 'SUPERADMINISTRADOR',
                'des_rol' => 'Rol principal del sistema. No puede ser eliminado ni suspendido.',
                'act_rol' => true,
            ],
            [
                'nom_rol' => 'ADMINISTRADOR',
                'des_rol' => 'Acceso total al sistema. Puede gestionar usuarios, parámetros y todos los módulos.',
                'act_rol' => true,
            ],
            [
                'nom_rol' => 'TECNICO',
                'des_rol' => 'Responsable de mapear rutas, tramos, obstáculos y evaluaciones de riesgo.',
                'act_rol' => true,
            ],
            [
                'nom_rol' => 'VOLUNTARIO',
                'des_rol' => 'Acompaña recorridos, reporta obstáculos y da seguimiento a afiliados.',
                'act_rol' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['nom_rol' => $role['nom_rol']],
                $role
            );
        }
    }
}