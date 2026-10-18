<?php
namespace Database\Seeders;

use App\Models\{PersonaNoVidente,User,Role,NivelSeguridad};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB,Auth};

/** Preparación explícita de prueba; nunca se ejecuta desde identificación móvil. */
class DemoMovilidadAfiliadoSeeder extends Seeder
{
    public function run(): void {
        if(!app()->environment(['local','testing']) || !str_contains(DB::connection()->getDatabaseName(),'testing'))
            throw new \RuntimeException('Prohibido crear datos de demostración en la base oficial.');
        DB::transaction(function() {
            $usuario=User::first();
            if(!$usuario) {
                $rol=Role::firstOrCreate(['nom_rol'=>'TECNICO'],['act_rol'=>true]);
                // Exclusivamente una cuenta técnica de fixture, sin contraseña conocida.
                $usuario=User::create(['ide_usu'=>'TEST-CREADOR-MOV','nom_usu'=>'PRUEBA','ape_usu'=>'MOVILIDAD',
                    'cii_usu'=>'TEST-CREADOR-MOV','cor_usu'=>'test.creador@ibc.invalid','con_usu'=>bcrypt(bin2hex(random_bytes(32))),
                    'cod_roles_usu'=>$rol->getKey(),'est_usu'=>'ACTIVO']);
            }
            $persona=PersonaNoVidente::firstOrCreate(['cii_pnv'=>'DEMO-MOVIL-001'],[
                'ide_pnv'=>'DEMO-MOVIL-001','nom_pnv'=>'PERSONA DEMOSTRACION','app_pnv'=>'PRUEBA','apm_pnv'=>'CONTROLADA',
                'ape_pnv'=>'PRUEBA CONTROLADA','fen_pnv'=>'1990-01-01','gen_pnv'=>'OTRO','gdi_pnv'=>'TOTAL','est_pnv'=>'ACTIVO']);
            if(!DB::table('afiliaciones')->where('ide_afi','DEMO-MOVIL-001')->exists()) {
                DB::table('afiliaciones')->insert(['ide_afi'=>'DEMO-MOVIL-001','cod_personas_no_videntes_afi'=>$persona->getKey(),
                    'cod_tipos_afiliado_afi'=>DB::table('tipos_afiliado')->value('cod_tipos_afiliado'),
                    'fia_afi'=>'2026-01-01','est_afi'=>'ACTIVA']);
            }
            if(!NivelSeguridad::where('niv_nse',5)->exists())
                throw new \RuntimeException('Falta el catálogo base de niveles de seguridad.');
            Auth::onceUsingId($usuario->getKey());
            $this->call(DemoLaPazSeeder::class);
        });
    }
}
