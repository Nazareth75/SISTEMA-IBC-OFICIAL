<?php

namespace Database\Seeders;

use App\Models\CalleTramo;
use App\Models\CondicionEntorno;
use App\Models\EvaluacionRiesgo;
use App\Models\NivelSeguridad;
use App\Models\Obstaculo;
use App\Models\ReporteObstaculo;
use App\Models\Ruta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RutaLaMercedSeeder extends Seeder
{
    public function run(): void
    {
        $archivo = database_path('data/ibc-merced-previa.json');
        if (!file_exists($archivo)) {
            return;
        }

        $data = json_decode(file_get_contents($archivo), true);
        if (empty($data['tramos'])) {
            return;
        }

        $ruta = Ruta::where('ide_rut', 'RUT-004')
            ->orWhere('nom_rut', 'like', '%HOSPITAL%MERCED%')
            ->first();

        $distanciaTotal = array_sum(array_column($data['tramos'], 'distancia_metros'));
        $trayectoCoords = [];
        foreach ($data['tramos'] as $t) {
            foreach ($t['geometria'] as $coord) {
                $trayectoCoords[] = ['lat' => (float)$coord['lat'], 'lng' => (float)$coord['lng']];
            }
        }

        if (!$ruta) {
            $ruta = Ruta::create([
                'ide_rut' => 'RUT-004',
                'nom_rut' => 'IBC SEDE CENTRAL - HOSPITAL MUNICIPAL LA MERCED',
                'des_rut' => 'Ruta urbana asistida accesible desde la Sede Central del IBC hasta el Hospital Municipal La Merced.',
                'ori_rut' => 'IBC Sede Central - Calle Colombia',
                'dst_rut' => 'Hospital Municipal La Merced - Villa Aspiazu',
                'geo_rut' => [
                    'distancia_metros' => round($distanciaTotal, 1),
                    'tiempo_minutos' => round($distanciaTotal / 75),
                    'origen_resuelto' => 'IBC Sede Central - Calle Colombia',
                    'destino_resuelto' => 'Hospital Municipal La Merced - Villa Aspiazu',
                    'origen_coords' => ['lat' => $data['puntos'][0]['lat'], 'lng' => $data['puntos'][0]['lng']],
                    'destino_coords' => ['lat' => $data['puntos'][1]['lat'], 'lng' => $data['puntos'][1]['lng']],
                    'trayecto_coords' => $trayectoCoords,
                    'tolerancia_topologica_m' => 8.0,
                ],
                'dis_rut' => round($distanciaTotal, 2),
                'tim_rut' => max(1, round($distanciaTotal / 75)),
                'est_rut' => 'PUBLICADA',
                'cod_usuarios_rut_cre' => 1,
            ]);

            foreach ($data['tramos'] as $idx => $t) {
                $tramo = CalleTramo::create([
                    'cod_rutas_ctr' => $ruta->getKey(),
                    'nom_ctr' => $t['nombre'],
                    'tip_ctr' => 'CALLE',
                    'ord_ctr' => $idx + 1,
                    'lon_ctr' => $t['distancia_metros'],
                    'geo_ctr' => $t['geometria'],
                    'act_ctr' => true,
                ]);

                DB::table('ruta_calles_tramos')->insert([
                    'cod_rutas_rct' => $ruta->getKey(),
                    'cod_calles_tramos_rct' => $tramo->getKey(),
                    'ord_rct' => $idx + 1,
                ]);
            }
        }

        $nivelBajo = NivelSeguridad::first();
        if ($nivelBajo) {
            foreach ($ruta->tramos as $tramo) {
                if ($tramo->evaluaciones()->count() === 0) {
                    EvaluacionRiesgo::create([
                        'cod_calles_tramos_eri' => $tramo->getKey(),
                        'cod_niveles_seguridad_eri' => $nivelBajo->getKey(),
                        'cod_usuarios_eri' => 1,
                        'fee_eri' => now()->toDateString(),
                        'obs_eri' => 'Evaluación técnica peatonal estándar.',
                        'fcr_eri' => now(),
                    ]);
                }
                if ($tramo->condiciones()->count() === 0) {
                    CondicionEntorno::create([
                        'cod_calles_tramos_cen' => $tramo->getKey(),
                        'tip_cen' => 'ILUMINACION',
                        'des_cen' => 'Alumbrado público LED operativo en acera.',
                        'fcr_cen' => now(),
                    ]);
                }
            }
        }
    }
}
