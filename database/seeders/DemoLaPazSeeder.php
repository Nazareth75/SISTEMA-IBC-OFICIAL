<?php

namespace Database\Seeders;

use App\Models\{CalleTramo, CondicionEntorno, EvaluacionRiesgo, NivelSeguridad, Ruta, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{Auth, DB};

/** Dataset adicional OSM: no purga ni reemplaza los registros anteriores. */
class DemoLaPazSeeder extends Seeder
{
    public function run(): void
    {
        $bd=DB::connection()->getDatabaseName();
        if(!app()->environment(['local','testing']) || !str_contains($bd,'testing'))
            throw new \RuntimeException('Este dataset solo puede sembrarse en la base de pruebas.');
        DB::transaction(function () {
            $datos = json_decode(file_get_contents(database_path('data/demo-la-paz.json')), true, 512, JSON_THROW_ON_ERROR);
            $usuario = Auth::id() ?? User::firstOrFail()->getKey();
            $nivel = NivelSeguridad::where('niv_nse', 5)->firstOrFail();
            $ruta = Ruta::firstOrCreate(['ide_rut' => 'DEMO-RUT-LP-OSM'], [
                'nom_rut' => $datos['nombre'], 'ori_rut' => $datos['origen'], 'dst_rut' => $datos['destino'],
                'des_rut' => '[DATOS SIMULADOS DE DEMOSTRACIÓN] '.$datos['advertencia'],
                'est_rut' => 'BORRADOR', 'cod_usuarios_rut_cre' => $usuario,
                'geo_rut' => ['origen_coords' => $datos['puntos'][0], 'destino_coords' => $datos['puntos'][1],
                    'tolerancia_topologica_m' => 1.0],
            ]);
            $pivotes = [];
            foreach ($datos['tramos'] as $i => $segmento) {
                $tramo = CalleTramo::firstOrCreate(['nom_ctr' => 'DEMO — LP '.($i + 1).' — '.$segmento['nombre']], [
                    'tip_ctr' => 'ACERA', 'lon_ctr' => $segmento['distancia_metros'],
                    'geo_ctr' => $segmento['geometria'], 'act_ctr' => true,
                ]);
                $pivotes[$tramo->getKey()] = ['ord_rct' => $i + 1];
                CondicionEntorno::firstOrCreate([
                    'cod_calles_tramos_cen' => $tramo->getKey(), 'des_cen' => '[DEMO] Condición simulada de pavimento.',
                ], ['tip_cen' => 'PAVIMENTO', 'niv_cen' => 'BUENO']);
                EvaluacionRiesgo::firstOrCreate([
                    'cod_calles_tramos_eri' => $tramo->getKey(), 'obs_eri' => '[DEMO] Evaluación simulada, sin inspección de campo.',
                ], ['cod_niveles_seguridad_eri' => $nivel->getKey(), 'cod_usuarios_eri' => $usuario,
                    'met_eri' => 'SIMULACIÓN', 'fee_eri' => '2026-10-18']);
            }
            $ruta->tramos()->syncWithoutDetaching($pivotes);
            app(\App\Services\Aco\ServicioEstadoRecomendacionAco::class)->limpiarEstado($ruta);
        });
    }
}
