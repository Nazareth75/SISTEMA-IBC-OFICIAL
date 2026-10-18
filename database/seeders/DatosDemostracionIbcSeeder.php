<?php

namespace Database\Seeders;

use App\Models\CalleTramo;
use App\Models\CondicionEntorno;
use App\Models\EvaluacionRiesgo;
use App\Models\NivelSeguridad;
use App\Models\Obstaculo;
use App\Models\ReporteObstaculo;
use App\Models\Ruta;
use App\Models\User;
use App\Models\VerificacionObstaculo;
use App\Models\ZonaRiesgo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder de datos simulados de demostración para el sistema IBC.
 *
 * Responsabilidad y alcance:
 * - Provee un conjunto enriquecido de datos sintéticos controlados para evidenciar
 *   y auditar visualmente las funcionalidades del sistema (módulo de rutas, tramos,
 *   evaluaciones, condiciones, zonas de riesgo, obstáculos y preparación topológica para ACO).
 * - CLASIFICACIÓN ESTRICTA: "DATOS SIMULADOS DE DEMOSTRACIÓN", no datos de campo.
 * - Todos los registros demo portan prefijos y patrones explícitos ('DEMO-', '[DEMO]').
 * - Reversibilidad total: permite sembrar, demostrar, purgar exclusivamente lo demo
 *   y volver a sembrar sin alterar RUT-001, RUT-002, RUT-003 ni los tramos 40–44.
 */
class DatosDemostracionIbcSeeder extends Seeder
{
    /**
     * Prefijos identificadores para aislamiento seguro.
     */
    public const PREFIJO_RUTA_IDE    = 'DEMO-RUT-';
    public const PREFIJO_TRAMO_NOM   = 'DEMO — ';
    public const PREFIJO_REPORTE_COD = 'DEMO-REP-';
    public const PREFIJO_ETIQUETA    = '[DEMO]';

    /**
     * Ejecuta el sembrado de demostración de forma idempotente y reversible.
     */
    public function run(): void
    {
        self::limpiarDemo();
        self::sembrarDemo();
    }

    /**
     * Purga exclusivamente los registros simulados de demostración en orden FK inverso.
     * Preserva intactos todos los registros oficiales (RUT-001, RUT-002, RUT-003, tramos 40–44, usuarios, etc.).
     */
    public static function limpiarDemo(): void
    {
        DB::transaction(function () {
            // 0. Verificaciones de obstáculos demo
            VerificacionObstaculo::whereHas('obstaculo', function ($q) {
                $q->where('des_obs', 'like', self::PREFIJO_ETIQUETA . '%');
            })->orWhere('observacion', 'like', self::PREFIJO_ETIQUETA . '%')->delete();

            // 1. Reportes de obstáculos demo
            ReporteObstaculo::where('codigo', 'like', self::PREFIJO_REPORTE_COD . '%')->delete();

            // 2. Obstáculos demo (forceDelete para remover soft-deletes)
            Obstaculo::where('des_obs', 'like', self::PREFIJO_ETIQUETA . '%')->forceDelete();

            // 3. Zonas de riesgo demo
            ZonaRiesgo::where('nom_zri', 'like', self::PREFIJO_TRAMO_NOM . '%')
                ->orWhere('des_zri', 'like', self::PREFIJO_ETIQUETA . '%')
                ->forceDelete();

            // 4. Evaluaciones de riesgo demo
            EvaluacionRiesgo::where('obs_eri', 'like', self::PREFIJO_ETIQUETA . '%')->forceDelete();

            // 5. Condiciones de entorno demo
            CondicionEntorno::where('des_cen', 'like', self::PREFIJO_ETIQUETA . '%')->forceDelete();

            // 6. Asociaciones N:M en ruta_calles_tramos
            DB::table('ruta_calles_tramos')
                ->whereIn('cod_rutas_rct', function ($q) {
                    $q->select('cod_rutas')->from('rutas')->where('ide_rut', 'like', self::PREFIJO_RUTA_IDE . '%');
                })
                ->orWhereIn('cod_calles_tramos_rct', function ($q) {
                    $q->select('cod_calles_tramos')->from('calles_tramos')->where('nom_ctr', 'like', self::PREFIJO_TRAMO_NOM . '%');
                })
                ->delete();

            // 7. Tramos físicos demo
            CalleTramo::where('nom_ctr', 'like', self::PREFIJO_TRAMO_NOM . '%')->forceDelete();

            // 8. Rutas demo
            Ruta::where('ide_rut', 'like', self::PREFIJO_RUTA_IDE . '%')->forceDelete();

            // 9. Usuario voluntario demo sintético si fue creado
            User::where('ide_usu', 'DEMO-USU-VOL')->delete();
        });
    }

    /**
     * Construye y persiste el conjunto completo de datos de demostración.
     */
    public static function sembrarDemo(): void
    {
        DB::transaction(function () {
            // ─── 0. Obtener dependencias base (Usuario y Niveles de Seguridad) ───
            $usuario = User::first();
            $usuarioId = $usuario ? $usuario->cod_usuarios : 1;

            $niveles = NivelSeguridad::all()->keyBy('niv_nse');
            $nivCritico = $niveles[1]?->cod_niveles_seguridad ?? 1;
            $nivBajo    = $niveles[2]?->cod_niveles_seguridad ?? 2;
            $nivMedio   = $niveles[3]?->cod_niveles_seguridad ?? 3;
            $nivAlto    = $niveles[4]?->cod_niveles_seguridad ?? 4;
            $nivOptimo  = $niveles[5]?->cod_niveles_seguridad ?? 5;

            // ─── 1. CREACIÓN DE TRAMOS FÍSICOS DEMOSTRATIVOS ─────────────────

            // Grupo A: Tramos para Ruta con Tramos pero SIN_DATOS (3 tramos lineales)
            $tSin1 = CalleTramo::create([
                'nom_ctr' => 'DEMO — TRAMO SIN DATOS 1 (SOPOCACHI)',
                'tip_ctr' => 'ACERA',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.510000, 'lng' => -68.130000], ['lat' => -16.511000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);
            $tSin2 = CalleTramo::create([
                'nom_ctr' => 'DEMO — TRAMO SIN DATOS 2 (SOPOCACHI)',
                'tip_ctr' => 'ACERA',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.511000, 'lng' => -68.130000], ['lat' => -16.512000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);
            $tSin3 = CalleTramo::create([
                'nom_ctr' => 'DEMO — TRAMO SIN DATOS 3 (SOPOCACHI)',
                'tip_ctr' => 'PASO PEATONAL',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.512000, 'lng' => -68.130000], ['lat' => -16.513000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);

            // Grupo B: Tramos para Ruta con Cobertura PARCIAL (2 tramos lineales)
            $tPar1 = CalleTramo::create([
                'nom_ctr' => 'DEMO — TRAMO COBERTURA PARCIAL 1',
                'tip_ctr' => 'ACERA',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.515000, 'lng' => -68.130000], ['lat' => -16.516000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);
            $tPar2 = CalleTramo::create([
                'nom_ctr' => 'DEMO — TRAMO COBERTURA PARCIAL 2',
                'tip_ctr' => 'CRUCE',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.516000, 'lng' => -68.130000], ['lat' => -16.517000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);

            // Grupo C: Tramos para Ruta Lineal con Información COMPLETA (3 tramos lineales)
            $tLin1 = CalleTramo::create([
                'nom_ctr' => 'DEMO — TRAMO LINEAL COMPLETO 1 (CALLE ROSENDO GUTIÉRREZ)',
                'tip_ctr' => 'ACERA',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.520000, 'lng' => -68.130000], ['lat' => -16.521000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);
            $tLin2 = CalleTramo::create([
                'nom_ctr' => 'DEMO — TRAMO LINEAL COMPLETO 2 (CALLE ECUADOR)',
                'tip_ctr' => 'CALLE',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.521000, 'lng' => -68.130000], ['lat' => -16.522000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);
            $tLin3 = CalleTramo::create([
                'nom_ctr' => 'DEMO — TRAMO LINEAL COMPLETO 3 (CALLE ASPIAZU)',
                'tip_ctr' => 'PASO PEATONAL',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.522000, 'lng' => -68.130000], ['lat' => -16.523000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);

            // Grupo D: Red de Grafo Topológico ACO (Bifurcaciones, caminos alternativos en Sopocachi)
            // N0: [-16.500000, -68.130000] Salida Plaza Abaroa
            // N1: [-16.501000, -68.130000] Intersección Sánchez Lima
            // N2: [-16.502000, -68.130000] (Camino A: Corto / Riesgoso por Sánchez Lima)
            // N3: [-16.502000, -68.127000] (Camino B: Largo / Seguro por Pedro Salazar y 20 de Octubre)
            // N6: [-16.502000, -68.132500] (Camino C: Intermedio por Pasaje Medinaceli)
            // N4: [-16.503000, -68.130000] (Confluencia hacia Belisario Salinas)
            // N5: [-16.504000, -68.130000] (Acceso Sede Central IBC en Guachalla)
            $tAcoComun1 = CalleTramo::create([
                'nom_ctr' => 'DEMO — Salida Plaza Abaroa por Calle Sánchez Lima [ACO Común Inicial]',
                'tip_ctr' => 'ACERA',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.500000, 'lng' => -68.130000], ['lat' => -16.501000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);
            $tAcoA1 = CalleTramo::create([
                'nom_ctr' => 'DEMO — Acera Norte Sánchez Lima hacia Aspiazu [ACO A1 - Corto/Riesgoso]',
                'tip_ctr' => 'CALLE',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.501000, 'lng' => -68.130000], ['lat' => -16.502000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);
            $tAcoA2 = CalleTramo::create([
                'nom_ctr' => 'DEMO — Cruce Peatonal Calzada Sánchez Lima y Salinas [ACO A2 - Corto/Riesgoso]',
                'tip_ctr' => 'CRUCE',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.502000, 'lng' => -68.130000], ['lat' => -16.503000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);
            $tAcoB1 = CalleTramo::create([
                'nom_ctr' => 'DEMO — Acera Sur Calle Pedro Salazar hacia 20 de Octubre [ACO B1 - Largo/Seguro]',
                'tip_ctr' => 'ACERA',
                'lon_ctr' => 340.50,
                'geo_ctr' => [
                    ['lat' => -16.501000, 'lng' => -68.130000],
                    ['lat' => -16.501500, 'lng' => -68.128500],
                    ['lat' => -16.502000, 'lng' => -68.127000]
                ],
                'act_ctr' => true,
            ]);
            $tAcoB2 = CalleTramo::create([
                'nom_ctr' => 'DEMO — Paso Peatonal Señalizado Av. 20 de Octubre [ACO B2 - Largo/Seguro]',
                'tip_ctr' => 'PASO PEATONAL',
                'lon_ctr' => 340.50,
                'geo_ctr' => [
                    ['lat' => -16.502000, 'lng' => -68.127000],
                    ['lat' => -16.502500, 'lng' => -68.128500],
                    ['lat' => -16.503000, 'lng' => -68.130000]
                ],
                'act_ctr' => true,
            ]);
            $tAcoC1 = CalleTramo::create([
                'nom_ctr' => 'DEMO — Rampa Peatonal Pasaje Medinaceli [ACO C1 - Intermedio]',
                'tip_ctr' => 'RAMPA',
                'lon_ctr' => 285.30,
                'geo_ctr' => [
                    ['lat' => -16.501000, 'lng' => -68.130000],
                    ['lat' => -16.501500, 'lng' => -68.131250],
                    ['lat' => -16.502000, 'lng' => -68.132500]
                ],
                'act_ctr' => true,
            ]);
            $tAcoC2 = CalleTramo::create([
                'nom_ctr' => 'DEMO — Paso Peatonal Escalinatas hacia Salinas [ACO C2 - Intermedio]',
                'tip_ctr' => 'ESCALERA',
                'lon_ctr' => 285.30,
                'geo_ctr' => [
                    ['lat' => -16.502000, 'lng' => -68.132500],
                    ['lat' => -16.502500, 'lng' => -68.131250],
                    ['lat' => -16.503000, 'lng' => -68.130000]
                ],
                'act_ctr' => true,
            ]);
            $tAcoComun2 = CalleTramo::create([
                'nom_ctr' => 'DEMO — Acceso Peatonal Final a Sede IBC Guachalla [ACO Común Final]',
                'tip_ctr' => 'ACERA',
                'lon_ctr' => 111.20,
                'geo_ctr' => [['lat' => -16.503000, 'lng' => -68.130000], ['lat' => -16.504000, 'lng' => -68.130000]],
                'act_ctr' => true,
            ]);

            // Grupo E: Tramo adicional para demostrar modelo N:M reusable
            $tComp1 = CalleTramo::create([
                'nom_ctr' => 'DEMO — Tramo Conector Compartido (Av. Arce hacia Miraflores)',
                'tip_ctr' => 'ACERA',
                'lon_ctr' => 180.00,
                'geo_ctr' => [['lat' => -16.502000, 'lng' => -68.127000], ['lat' => -16.503500, 'lng' => -68.127000]],
                'act_ctr' => true,
            ]);

            // ─── 2. CREACIÓN DE RUTAS DEMOSTRATIVAS ──────────────────────────

            // RUTA 1: Sin tramos (Demostración de Ruta Eliminable sin dependencias)
            $r1 = Ruta::create([
                'ide_rut'              => 'DEMO-RUT-001',
                'nom_rut'              => 'DEMO — RUTA SIN TRAMOS ASOCIADOS (PRUEBA ELIMINABLE)',
                'des_rut'              => '[DATOS SIMULADOS DE DEMOSTRACIÓN] Ruta de prueba representativa de una ruta recién creada sin segmentos viales vinculados, completamente eliminable para pruebas.',
                'ori_rut'              => 'Plaza del Estudiante',
                'dst_rut'              => 'Edificio Central IBC',
                'dis_rut'              => 0.00,
                'est_rut'              => 'BORRADOR',
                'cod_usuarios_rut_cre' => $usuarioId,
            ]);

            // RUTA 2: Con tramos pero SIN_DATOS
            $r2 = Ruta::create([
                'ide_rut'              => 'DEMO-RUT-002',
                'nom_rut'              => 'DEMO — RUTA CON TRAMOS SIN EVALUACIÓN',
                'des_rut'              => '[DATOS SIMULADOS DE DEMOSTRACIÓN] Ruta lineal con geometría y tramos asociados pero sin datos de evaluaciones, condiciones, zonas ni obstáculos.',
                'ori_rut'              => 'Av. 20 de Octubre',
                'dst_rut'              => 'Plaza Abaroa',
                'dis_rut'              => 333.60,
                'est_rut'              => 'EN REVISIÓN',
                'cod_usuarios_rut_cre' => $usuarioId,
            ]);
            $r2->tramos()->attach([
                $tSin1->cod_calles_tramos => ['ord_rct' => 1],
                $tSin2->cod_calles_tramos => ['ord_rct' => 2],
                $tSin3->cod_calles_tramos => ['ord_rct' => 3],
            ]);

            // RUTA 3: Cobertura de riesgo PARCIAL
            $r3 = Ruta::create([
                'ide_rut'              => 'DEMO-RUT-003',
                'nom_rut'              => 'DEMO — RUTA CON COBERTURA PARCIAL',
                'des_rut'              => '[DATOS SIMULADOS DE DEMOSTRACIÓN] Ruta de demostración donde los tramos cuentan con evaluaciones y condiciones pero carecen de zonas u obstáculos.',
                'ori_rut'              => 'Plaza España',
                'dst_rut'              => 'Calle Belisario Salinas',
                'dis_rut'              => 222.40,
                'est_rut'              => 'EN REVISIÓN',
                'cod_usuarios_rut_cre' => $usuarioId,
            ]);
            $r3->tramos()->attach([
                $tPar1->cod_calles_tramos => ['ord_rct' => 1],
                $tPar2->cod_calles_tramos => ['ord_rct' => 2],
            ]);

            // RUTA 4: Lineal con información COMPLETA (Trayectoria Única)
            $r4 = Ruta::create([
                'ide_rut'              => 'DEMO-RUT-004',
                'nom_rut'              => 'DEMO — RUTA LINEAL CON INFORMACIÓN COMPLETA',
                'des_rut'              => '[DATOS SIMULADOS DE DEMOSTRACIÓN] Ruta lineal con cobertura total en sus 4 dimensiones de riesgo, demostrando estado no optimizable por trayectoria única.',
                'ori_rut'              => 'Calle Rosendo Gutiérrez',
                'dst_rut'              => 'Calle Aspiazu',
                'dis_rut'              => 333.60,
                'est_rut'              => 'APROBADA',
                'cod_usuarios_rut_cre' => $usuarioId,
                'cod_usuarios_rut_apr' => $usuarioId,
                'fap_rut'              => now(),
            ]);
            $r4->tramos()->attach([
                $tLin1->cod_calles_tramos => ['ord_rct' => 1],
                $tLin2->cod_calles_tramos => ['ord_rct' => 2],
                $tLin3->cod_calles_tramos => ['ord_rct' => 3],
            ]);

            // RUTA 5: Topología de Grafo ACO con alternativas
            $r5 = Ruta::create([
                'ide_rut'              => 'DEMO-RUT-005',
                'nom_rut'              => 'DEMO ACO — RED CON BIFURCACIÓN Y ALTERNATIVAS (SOPOCACHI)',
                'des_rut'              => '[DATOS SIMULADOS DE DEMOSTRACIÓN] Red peatonal para ACO en Sopocachi con salida Plaza Abaroa, bifurcación en 3 caminos alternativos y confluencia en Calle Guachalla (Sede IBC).',
                'ori_rut'              => 'Plaza Abaroa (Sopocachi)',
                'dst_rut'              => 'Sede Central IBC (Calle Guachalla)',
                'dis_rut'              => 1700.40,
                'est_rut'              => 'BORRADOR',
                'cod_usuarios_rut_cre' => $usuarioId,
            ]);
            $r5->tramos()->attach([
                $tAcoComun1->cod_calles_tramos => ['ord_rct' => 1],
                $tAcoA1->cod_calles_tramos     => ['ord_rct' => 2],
                $tAcoA2->cod_calles_tramos     => ['ord_rct' => 3],
                $tAcoB1->cod_calles_tramos     => ['ord_rct' => 4],
                $tAcoB2->cod_calles_tramos     => ['ord_rct' => 5],
                $tAcoC1->cod_calles_tramos     => ['ord_rct' => 6],
                $tAcoC2->cod_calles_tramos     => ['ord_rct' => 7],
                $tAcoComun2->cod_calles_tramos => ['ord_rct' => 8],
            ]);

            // RUTA 6: Demostración de tramos reutilizados (N:M)
            $r6 = Ruta::create([
                'ide_rut'              => 'DEMO-RUT-006',
                'nom_rut'              => 'DEMO — RUTA ALTERNA CON TRAMOS COMPARTIDOS (SOPOCACHI-MIRAFLORES)',
                'des_rut'              => '[DATOS SIMULADOS DE DEMOSTRACIÓN] Ruta que reutiliza tramos compartidos de la red ACO (Pedro Salazar B1 y B2) demostrando la relación N:M.',
                'ori_rut'              => 'Calle Pedro Salazar (Bifurcación Este)',
                'dst_rut'              => 'Miraflores Conector',
                'dis_rut'              => 861.00,
                'est_rut'              => 'BORRADOR',
                'cod_usuarios_rut_cre' => $usuarioId,
            ]);
            $r6->tramos()->attach([
                $tAcoB1->cod_calles_tramos => ['ord_rct' => 1],
                $tAcoB2->cod_calles_tramos => ['ord_rct' => 2],
                $tComp1->cod_calles_tramos => ['ord_rct' => 3],
            ]);

            // ─── 3. CONDICIONES DEL ENTORNO (20 a 40 registros) ───────────────
            // Cubre estados: BUENO, REGULAR, MALO y tipos oficiales
            $condicionesData = [
                // Para Ruta Parcial
                [$tPar1->cod_calles_tramos, 'PAVIMENTO', '[DEMO] Pavimento de baldosas con desgaste moderado', 'REGULAR'],
                [$tPar1->cod_calles_tramos, 'ILUMINACIÓN', '[DEMO] Luminarias led operativas cada 25m', 'BUENO'],
                [$tPar2->cod_calles_tramos, 'ANCHO VEREDA', '[DEMO] Vereda estrecha menor a 1.20 metros', 'REGULAR'],
                [$tPar2->cod_calles_tramos, 'SEÑALÉTICA', '[DEMO] Falta señalización podotáctil en cruce', 'MALO'],

                // Para Ruta Lineal Completa
                [$tLin1->cod_calles_tramos, 'PAVIMENTO', '[DEMO] Pavimento uniforme recién remodelado', 'BUENO'],
                [$tLin1->cod_calles_tramos, 'ANCHO VEREDA', '[DEMO] Vereda ancha superior a 2.5 metros', 'BUENO'],
                [$tLin2->cod_calles_tramos, 'PENDIENTE', '[DEMO] Pendiente moderada del 8% accesible', 'REGULAR'],
                [$tLin2->cod_calles_tramos, 'ILUMINACIÓN', '[DEMO] Iluminación nocturna deficiente', 'MALO'],
                [$tLin3->cod_calles_tramos, 'SEÑALÉTICA', '[DEMO] Señalización sonora en semáforo peatonal', 'BUENO'],
                [$tLin3->cod_calles_tramos, 'PAVIMENTO', '[DEMO] Baldosa podotáctil de advertencia completa', 'BUENO'],

                // Para Red ACO - Tramo Común 1
                [$tAcoComun1->cod_calles_tramos, 'PAVIMENTO', '[DEMO] Acera de concreto en buen estado', 'BUENO'],
                [$tAcoComun1->cod_calles_tramos, 'ILUMINACIÓN', '[DEMO] Buena visibilidad y postes alineados', 'BUENO'],

                // Para Red ACO - Camino A (Corto pero muy riesgoso)
                [$tAcoA1->cod_calles_tramos, 'PAVIMENTO', '[DEMO] Pavimento roto con desniveles profundos', 'MALO'],
                [$tAcoA1->cod_calles_tramos, 'ANCHO VEREDA', '[DEMO] Vereda angosta invadida por comercio', 'MALO'],
                [$tAcoA1->cod_calles_tramos, 'ILUMINACIÓN', '[DEMO] Focos quemados, tramo a oscuras', 'MALO'],
                [$tAcoA2->cod_calles_tramos, 'PENDIENTE', '[DEMO] Bajada pronunciada sin pasamanos de apoyo', 'MALO'],
                [$tAcoA2->cod_calles_tramos, 'SEÑALÉTICA', '[DEMO] Cruce sin pintura de cebra ni señal ética', 'MALO'],

                // Para Red ACO - Camino B (Largo pero muy seguro)
                [$tAcoB1->cod_calles_tramos, 'PAVIMENTO', '[DEMO] Baldosa podotáctil continua de guía direccional', 'BUENO'],
                [$tAcoB1->cod_calles_tramos, 'ANCHO VEREDA', '[DEMO] Paseo peatonal amplio libre de obstáculos', 'BUENO'],
                [$tAcoB1->cod_calles_tramos, 'ILUMINACIÓN', '[DEMO] Luminarias solares inteligentes', 'BUENO'],
                [$tAcoB2->cod_calles_tramos, 'SEÑALÉTICA', '[DEMO] Cruce semaforizado con aviso acústico', 'BUENO'],
                [$tAcoB2->cod_calles_tramos, 'PENDIENTE', '[DEMO] Pendiente nula, superficie plana y regular', 'BUENO'],

                // Para Red ACO - Camino C (Intermedio)
                [$tAcoC1->cod_calles_tramos, 'OTRO', '[DEMO] Rampa peatonal con pendiente estándar', 'REGULAR'],
                [$tAcoC1->cod_calles_tramos, 'ILUMINACIÓN', '[DEMO] Luminaria pública estándar', 'REGULAR'],
                [$tAcoC2->cod_calles_tramos, 'PAVIMENTO', '[DEMO] Gradas con cantoneras antideslizantes', 'REGULAR'],
                [$tAcoC2->cod_calles_tramos, 'ANCHO VEREDA', '[DEMO] Ancho suficiente con baranda izquierda', 'BUENO'],

                // Para Red ACO - Tramo Común 2
                [$tAcoComun2->cod_calles_tramos, 'PAVIMENTO', '[DEMO] Acceso pavimentado a institución', 'BUENO'],
                [$tAcoComun2->cod_calles_tramos, 'ILUMINACIÓN', '[DEMO] Faroles institucionales encendidos', 'BUENO'],

                // Tramo conector
                [$tComp1->cod_calles_tramos, 'PAVIMENTO', '[DEMO] Adoquín tradicional en estado regular', 'REGULAR'],
            ];

            foreach ($condicionesData as $c) {
                $tipo = is_array($c[1]) ? array_key_first($c[1]) : $c[1];
                if (!in_array($tipo, ['PAVIMENTO','ILUMINACIÓN','SEÑALÉTICA','ANCHO VEREDA','PENDIENTE','OTRO'])) {
                    $tipo = 'OTRO';
                }
                CondicionEntorno::create([
                    'cod_calles_tramos_cen' => $c[0],
                    'tip_cen'               => $tipo,
                    'des_cen'               => $c[2],
                    'niv_cen'               => $c[3],
                ]);
            }

            // ─── 4. EVALUACIONES DE RIESGO (15 a 25 registros) ───────────────
            // Cubre los 5 niveles: 1=CRÍTICO, 2=BAJO, 3=MEDIO, 4=ALTO, 5=ÓPTIMO
            $evaluacionesData = [
                // Ruta Parcial
                [$tPar1->cod_calles_tramos, $nivMedio, 'INSPECCIÓN TÉCNICA VISUAL', '[DEMO] Evaluación técnica intermedia de seguridad peatonal.'],
                [$tPar2->cod_calles_tramos, $nivBajo,  'AUDITORÍA DE ACCESIBILIDAD', '[DEMO] Deficiencias detectadas en cruce peatonal.'],

                // Ruta Lineal Completa
                [$tLin1->cod_calles_tramos, $nivOptimo, 'EVALUACIÓN INTEGRAL IBC', '[DEMO] Condiciones de máxima seguridad para invidentes.'],
                [$tLin2->cod_calles_tramos, $nivAlto,   'INSPECCIÓN RUTINARIA',    '[DEMO] Tramo seguro con precauciones menores.'],
                [$tLin3->cod_calles_tramos, $nivOptimo, 'CERTIFICACIÓN INCLUSIVA', '[DEMO] Vía completamente adaptada y segura.'],

                // Red ACO - Tramo Común 1
                [$tAcoComun1->cod_calles_tramos, $nivAlto, 'INSPECCIÓN TÉCNICA', '[DEMO] Acceso común adecuado con baja accidentalidad.'],

                // Red ACO - Camino A (Corto pero CRÍTICO)
                [$tAcoA1->cod_calles_tramos, $nivCritico, 'AUDITORÍA DE SEGURIDAD', '[DEMO] Tramo altamente peligroso: baches y comercio informal.'],
                [$tAcoA2->cod_calles_tramos, $nivCritico, 'INSPECCIÓN DE RIESGO',   '[DEMO] Cruce crítico sin protección para no videntes.'],

                // Red ACO - Camino B (Largo pero ÓPTIMO)
                [$tAcoB1->cod_calles_tramos, $nivOptimo, 'CERTIFICACIÓN PODOTÁCTIL', '[DEMO] Tramo modelo con guiado continuo podotáctil.'],
                [$tAcoB2->cod_calles_tramos, $nivOptimo, 'EVALUACIÓN SEMAFÓRICA',    '[DEMO] Intersección protegida con señal acústica sincronizada.'],

                // Red ACO - Camino C (Intermedio)
                [$tAcoC1->cod_calles_tramos, $nivMedio, 'INSPECCIÓN DE RAMPAS',     '[DEMO] Pendiente moderada con dificultad media.'],
                [$tAcoC2->cod_calles_tramos, $nivMedio, 'INSPECCIÓN DE ESCALONES',  '[DEMO] Gradas transitables con apoyo de pasamanos.'],

                // Red ACO - Tramo Común 2
                [$tAcoComun2->cod_calles_tramos, $nivOptimo, 'EVALUACIÓN ENTRADA IBC', '[DEMO] Acceso final seguro y señalizado.'],

                // Tramo conector
                [$tComp1->cod_calles_tramos, $nivAlto, 'INSPECCIÓN MIRAFLORES', '[DEMO] Segmento seguro con baja afluencia vehicular.'],

                // Evaluaciones históricas (para validar ordenamiento cronológico)
                [$tAcoA1->cod_calles_tramos, $nivBajo,   'INSPECCIÓN PREVIA 2025', '[DEMO] Evaluación histórica previa a deterioro.'],
                [$tAcoB1->cod_calles_tramos, $nivAlto,   'INSPECCIÓN PREVIA 2025', '[DEMO] Evaluación histórica previa a mejoras podotáctiles.'],
            ];

            foreach ($evaluacionesData as $idx => $ev) {
                EvaluacionRiesgo::create([
                    'cod_calles_tramos_eri'     => $ev[0],
                    'cod_niveles_seguridad_eri' => $ev[1],
                    'met_eri'                   => $ev[2],
                    'obs_eri'                   => $ev[3],
                    'cod_usuarios_eri'          => $usuarioId,
                    'fee_eri'                   => now()->subDays(count($evaluacionesData) - $idx)->toDateString(),
                ]);
            }

            // ─── 5. ZONAS DE RIESGO ACTIVAS (8 a 15 registros) ───────────────
            $zonasData = [
                // Ruta Lineal Completa
                [$tLin2->cod_calles_tramos, 'DEMO — ZONA DE OBRAS VIALES', $nivMedio, '[DEMO] Reparación de tuberías subterráneas en calzada.', true],
                [$tLin3->cod_calles_tramos, 'DEMO — ZONA ESCOLAR TRANSITADA', $nivAlto, '[DEMO] Flujo escolar en horarios punta de mediodía.', true],

                // Red ACO - Camino A (Zonas activas críticas)
                [$tAcoA1->cod_calles_tramos, 'DEMO — ZONA COMERCIO AMBULANTE INFORMAL', $nivCritico, '[DEMO] Vendedores apostados sobre la acera bloqueando paso.', true],
                [$tAcoA1->cod_calles_tramos, 'DEMO — ZONA TRÁFICO VEHICULAR INTENSO', $nivCritico, '[DEMO] Giro constante de minibuses a velocidad.', true],
                [$tAcoA2->cod_calles_tramos, 'DEMO — ZONA HUNDIMIENTO DE CALZADA', $nivCritico, '[DEMO] Deformación de vereda por filtración pluvial.', true],

                // Red ACO - Camino C (Zona moderada)
                [$tAcoC1->cod_calles_tramos, 'DEMO — ZONA PENDIENTE HÚMEDA', $nivMedio, '[DEMO] Superficie resbalosa durante días de lluvia.', true],
                [$tAcoC2->cod_calles_tramos, 'DEMO — ZONA ESCALINATA PEATONAL', $nivMedio, '[DEMO] Desnivel pronunciado de 18 peldaños.', true],

                // Tramo conector
                [$tComp1->cod_calles_tramos, 'DEMO — ZONA PARADA DE BUSES', $nivBajo, '[DEMO] Congestión momentánea en parada de transporte.', true],

                // Zonas inactivas (para probar filtros de act_zri)
                [$tAcoB1->cod_calles_tramos, 'DEMO — ZONA TRABAJOS FINALIZADOS', $nivOptimo, '[DEMO] Antiguo tendido eléctrico ya concluido.', false],
                [$tLin1->cod_calles_tramos,  'DEMO — ZONA PODA DE ÁRBOLES HISTÓRICA', $nivAlto, '[DEMO] Mantenimiento forestal finalizado.', false],
            ];

            foreach ($zonasData as $z) {
                ZonaRiesgo::create([
                    'nom_zri'                   => $z[1],
                    'cod_calles_tramos_zri'     => $z[0],
                    'cod_niveles_seguridad_zri' => $z[2],
                    'des_zri'                   => $z[3],
                    'act_zri'                   => $z[4],
                ]);
            }

            // ─── 6. OBSTÁCULOS (15 a 30 registros) ───────────────────────────
            // Cubre: POSTE, ESCALÓN, OBRA, VEHÍCULO, BACHE, VENDEDOR, OTRO
            // Estados: ACTIVO, REPORTADO, RESUELTO
            // Orígenes: TECNICO, VOLUNTARIO, AFILIADO
            $obstaculosData = [
                // Ruta Lineal Completa (activos en todos los tramos para cobertura completa, más 1 resuelto)
                [$tLin1->cod_calles_tramos, 'POSTE', '[DEMO] Poste de señalética ladeado hacia la vereda', 'REPORTADO', 'VOLUNTARIO'],
                [$tLin2->cod_calles_tramos, 'OBRA',  '[DEMO] Excavación canalizada con valla de contención', 'ACTIVO', 'TECNICO'],
                [$tLin3->cod_calles_tramos, 'POSTE', '[DEMO] Poste con base deteriorada en vereda', 'ACTIVO', 'TECNICO'],
                [$tLin3->cod_calles_tramos, 'BACHE', '[DEMO] Hueco en acera reparado con cemento', 'RESUELTO', 'TECNICO'],

                // Red ACO - Tramo Común 1 (0 obstáculos activos, 1 resuelto)
                [$tAcoComun1->cod_calles_tramos, 'VEHÍCULO', '[DEMO] Auto estacionado sobre la acera ya retirado', 'RESUELTO', 'AFILIADO'],

                // Red ACO - Camino A (Múltiples obstáculos activos y reportados: alto riesgo)
                [$tAcoA1->cod_calles_tramos, 'POSTE',    '[DEMO] Poste de luz en mitad del sendero peatonal', 'ACTIVO', 'TECNICO'],
                [$tAcoA1->cod_calles_tramos, 'VENDEDOR', '[DEMO] Puesto de venta informal ocupando ancho de vereda', 'ACTIVO', 'AFILIADO'],
                [$tAcoA1->cod_calles_tramos, 'BACHE',    '[DEMO] Adoquines sueltos con riesgo de tropiezo', 'REPORTADO', 'VOLUNTARIO'],
                [$tAcoA2->cod_calles_tramos, 'ESCALÓN',  '[DEMO] Escalón sin rampa ni señalización táctil', 'ACTIVO', 'TECNICO'],
                [$tAcoA2->cod_calles_tramos, 'OBRA',     '[DEMO] Escombros de demolición acumulados en esquina', 'ACTIVO', 'VOLUNTARIO'],
                [$tAcoA2->cod_calles_tramos, 'VEHÍCULO', '[DEMO] Minibuses esperando pasajeros sobre cruce peatonal', 'REPORTADO', 'AFILIADO'],

                // Red ACO - Camino B (Seguro: 0 obstáculos activos, 2 resueltos históricos)
                [$tAcoB1->cod_calles_tramos, 'POSTE',   '[DEMO] Antiguo poste reubicado fuera de la línea de tránsito', 'RESUELTO', 'TECNICO'],
                [$tAcoB2->cod_calles_tramos, 'ESCALÓN', '[DEMO] Escalón sustituido por rampa reglamentaria', 'RESUELTO', 'TECNICO'],

                // Red ACO - Camino C (Intermedio: 1 activo, 1 reportado)
                [$tAcoC1->cod_calles_tramos, 'OTRO',    '[DEMO] Toldo bajo de comercio comercial a 1.60m de altura', 'ACTIVO', 'AFILIADO'],
                [$tAcoC2->cod_calles_tramos, 'ESCALÓN', '[DEMO] Peldaño deteriorado al inicio de la escalinata', 'REPORTADO', 'VOLUNTARIO'],

                // Red ACO - Tramo Común 2 (Seguro: 0 activos, 1 resuelto)
                [$tAcoComun2->cod_calles_tramos, 'BACHE', '[DEMO] Bache asfaltado por cuadrilla municipal', 'RESUELTO', 'TECNICO'],

                // Tramo conector
                [$tComp1->cod_calles_tramos, 'VEHÍCULO', '[DEMO] Motocicleta estacionada temporalmente en vereda', 'REPORTADO', 'VOLUNTARIO'],
                [$tComp1->cod_calles_tramos, 'POSTE',    '[DEMO] Cartel publicitario colocado en la pared', 'ACTIVO', 'AFILIADO'],
            ];

            $obstaculosCreados = [];
            foreach ($obstaculosData as $idx => $obs) {
                $ob = Obstaculo::create([
                    'cod_calles_tramos_obs' => $obs[0],
                    'tip_obs'               => $obs[1],
                    'des_obs'               => $obs[2],
                    'est_obs'               => $obs[3],
                    'ori_obs'               => $obs[4],
                    'cod_usuarios_obs'      => $usuarioId,
                ]);
                $obstaculosCreados[] = $ob;
            }

            // ─── 6.1. VERIFICACIONES COLABORATIVAS DE DEMOSTRACIÓN ───────────
            // Se asocian al obstáculo activo en el tramo A1 de DEMO-RUT-005 para evidenciar
            // el consenso comunitario ("POSIBLEMENTE_RESUELTO") y la bandeja de confirmación técnica.
            if (isset($obstaculosCreados[5])) {
                $obConsenso = $obstaculosCreados[5];
                $usuariosExistentes = User::take(2)->pluck('cod_usuarios')->all();
                $usu1Id = $usuariosExistentes[0] ?? $usuarioId;
                $usu2Id = $usuariosExistentes[1] ?? null;

                if (!$usu2Id) {
                    $rolVol = \App\Models\Role::where('nom_rol', 'VOLUNTARIO')->value('cod_roles') ?? 3;
                    $segundoUsu = User::create([
                        'ide_usu'       => 'DEMO-USU-VOL',
                        'nom_usu'       => 'DANIELA',
                        'ape_usu'       => 'CHOQUE ROJAS',
                        'cii_usu'       => '7890123',
                        'cor_usu'       => 'voluntario.demo@ibc.bo',
                        'con_usu'       => bcrypt('password'),
                        'cod_roles_usu' => $rolVol,
                        'est_usu'       => true,
                    ]);
                    $usu2Id = $segundoUsu->cod_usuarios;
                }

                VerificacionObstaculo::create([
                    'cod_obstaculo' => $obConsenso->cod_obstaculos,
                    'cod_usuario'   => $usu1Id,
                    'respuesta'     => 'YA_NO_ESTA',
                    'latitud'       => -16.501100,
                    'longitud'      => -68.130000,
                    'precision_gps' => 4.2,
                    'observacion'   => '[DEMO] Evidencia colaborativa: acera despejada y obstáculo retirado.',
                    'canal'         => 'APP',
                    'created_at'    => now()->subHours(2),
                ]);

                VerificacionObstaculo::create([
                    'cod_obstaculo' => $obConsenso->cod_obstaculos,
                    'cod_usuario'   => $usu2Id,
                    'respuesta'     => 'YA_NO_ESTA',
                    'latitud'       => -16.501120,
                    'longitud'      => -68.130010,
                    'precision_gps' => 3.5,
                    'observacion'   => '[DEMO] Verificación en campo: paso libre sin impedimento para bastón.',
                    'canal'         => 'WEB',
                    'created_at'    => now()->subHour(),
                ]);
            }

            // ─── 7. REPORTES DE INCIDENCIAS / BASTÓN (20 a 40 registros) ─────
            // Cubre métodos: MANUAL, BASTON (marcado explícito), IA
            // Cubre estados: REPORTADO, ACTIVO, RESUELTO, DESCARTADO
            // Cubre prioridades: 1, 2, 3, 4
            $reportesData = [
                // Bastón inteligente simulado (claramente marcado como demostración)
                ['DEMO-REP-001', 'POSTE', 'SIMULACIÓN DE DETECCIÓN POR BASTÓN — Sensor ultrasónico detectó obstáculo vertical a 45 cm', 'Av. 20 de Octubre esq. Aspiazu', -16.501500, -68.130000, 'CRITICO', 4, 'BASTON', 'ACTIVO', 4],
                ['DEMO-REP-002', 'ESCALÓN', 'SIMULACIÓN DE DETECCIÓN POR BASTÓN — Sensor ToF inferior detectó desnivel abrupto de 15 cm', 'Calle Rosendo Gutiérrez esq. Ecuador', -16.502300, -68.130000, 'ALTO', 3, 'BASTON', 'ACTIVO', 7],
                ['DEMO-REP-003', 'VEHÍCULO', 'SIMULACIÓN DE DETECCIÓN POR BASTÓN — Eco de proximidad por objeto voluminoso metálico en vereda', 'Calle Aspiazu frente a farmacia', -16.502800, -68.130000, 'ALTO', 3, 'BASTON', 'REPORTADO', 9],
                ['DEMO-REP-004', 'BACHE', 'SIMULACIÓN DE DETECCIÓN POR BASTÓN — Depresión en pavimento detectada en barrido de puntera', 'Calle Guachalla pasaje peatonal', -16.501800, -68.130000, 'MEDIO', 2, 'BASTON', 'ACTIVO', 6],
                ['DEMO-REP-005', 'OBRA', 'SIMULACIÓN DE DETECCIÓN POR BASTÓN — Detección de perímetro cerrado con cintas y balizas', 'Plaza Abaroa acera este', -16.502900, -68.130000, 'CRITICO', 4, 'BASTON', 'ACTIVO', 8],
                ['DEMO-REP-006', 'VENDEDOR', 'SIMULACIÓN DE DETECCIÓN POR BASTÓN — Interferencia continua por multitud u ocupación estática', 'Av. Sánchez Lima', -16.501200, -68.130000, 'MEDIO', 2, 'BASTON', 'ACTIVO', 5],

                // Reportes Manuales (Web / App)
                ['DEMO-REP-007', 'POSTE', 'Reporte manual realizado por voluntario sobre poste fuera de norma', 'Calle Fernando Guachalla', -16.520500, -68.130000, 'MEDIO', 2, 'MANUAL', 'REPORTADO', 0],
                ['DEMO-REP-008', 'OBRA', 'Zanja de alcantarillado abierta sin pasarela provisoria segura', 'Calle Belisario Salinas', -16.521500, -68.130000, 'CRITICO', 4, 'MANUAL', 'ACTIVO', 1],
                ['DEMO-REP-009', 'BACHE', 'Adoquín faltante ya reparado satisfactoriamente por personal de maestranza', 'Av. Arce esq. Pinilla', -16.522500, -68.130000, 'BAJO', 1, 'MANUAL', 'RESUELTO', 2],
                ['DEMO-REP-010', 'VEHÍCULO', 'Vehículo bloqueando rampa de acceso para personas con discapacidad', 'Plaza España acera norte', -16.500500, -68.130000, 'ALTO', 3, 'MANUAL', 'RESUELTO', 3],
                ['DEMO-REP-011', 'OTRO', 'Ramas de árbol colgando a baja altura golpeando la cabeza de transeúntes', 'Calle Capitán Ravelo', -16.501600, -68.131500, 'MEDIO', 2, 'MANUAL', 'ACTIVO', 12],
                ['DEMO-REP-012', 'ESCALÓN', 'Peldaño quebrado con varilla de hierro expuesta en escalinata', 'Calle Montevideo', -16.502400, -68.132000, 'ALTO', 3, 'MANUAL', 'REPORTADO', 13],
                ['DEMO-REP-013', 'BACHE', 'Reparación de vereda ya sellada y transitable', 'Av. 6 de Agosto', -16.503200, -68.130000, 'BAJO', 1, 'MANUAL', 'RESUELTO', 14],
                ['DEMO-REP-014', 'VEHÍCULO', 'Repartidor en motocicleta sobre la acera', 'Plaza Uyuni', -16.502200, -68.127000, 'MEDIO', 2, 'MANUAL', 'REPORTADO', 15],
                ['DEMO-REP-015', 'POSTE', 'Poste con cables sueltos descolgados', 'Av. Saavedra', -16.502600, -68.127000, 'ALTO', 3, 'MANUAL', 'ACTIVO', 16],

                // Reportes por Reconocimiento IA
                ['DEMO-REP-016', 'POSTE', 'Reconocimiento visual IA: detección de columna de cemento en ruta accesible', 'Av. 20 de Octubre', -16.501500, -68.130000, 'MEDIO', 2, 'IA', 'ACTIVO', 4],
                ['DEMO-REP-017', 'VENDEDOR', 'Reconocimiento visual IA: detección de sombrilla y tarima de venta ambulante', 'Av. 20 de Octubre', -16.501300, -68.130000, 'ALTO', 3, 'IA', 'ACTIVO', 5],
                ['DEMO-REP-018', 'OBRA', 'Reconocimiento visual IA: malla de seguridad naranja de obra en construcción', 'Calle Aspiazu', -16.502900, -68.130000, 'CRITICO', 4, 'IA', 'ACTIVO', 8],
                ['DEMO-REP-019', 'BACHE', 'Reconocimiento visual IA: hueco en pavimento con agua estancada', 'Calle Rosendo Gutiérrez', -16.502100, -68.130000, 'MEDIO', 2, 'IA', 'ACTIVO', 6],
                ['DEMO-REP-020', 'ESCALÓN', 'Reconocimiento visual IA: peldaño no coincidente con huella estándar', 'Calle Rosendo Gutiérrez', -16.502200, -68.130000, 'ALTO', 3, 'IA', 'ACTIVO', 7],

                // Reportes Descartados (para verificar que no afecten prioridad máxima en TramoRiskService)
                ['DEMO-REP-021', 'OTRO', 'Falsa alarma: sombra proyectada interpretada erróneamente como obstáculo', 'Av. 20 de Octubre', -16.501400, -68.130000, 'BAJO', 4, 'IA', 'DESCARTADO', 4],
                ['DEMO-REP-022', 'POSTE', 'Reporte duplicado descartado por el equipo técnico de campo', 'Calle Aspiazu', -16.502700, -68.130000, 'MEDIO', 4, 'MANUAL', 'DESCARTADO', 7],
            ];

            foreach ($reportesData as $rep) {
                $obsAsoc = isset($obstaculosCreados[$rep[10]]) ? $obstaculosCreados[$rep[10]]->cod_obstaculos : null;

                ReporteObstaculo::create([
                    'codigo'                 => $rep[0],
                    'tipo_obstaculo'         => $rep[1],
                    'descripcion'            => $rep[2],
                    'direccion_referencia'   => $rep[3],
                    'latitud'                => $rep[4],
                    'longitud'               => $rep[5],
                    'nivel_riesgo'           => $rep[6],
                    'prioridad'              => $rep[7],
                    'metodo_deteccion'       => $rep[8],
                    'estado'                 => $rep[9],
                    'reportado_por'          => $usuarioId,
                    'validado_por'           => $usuarioId,
                    'cod_obstaculos_rpo'     => $obsAsoc,
                    'fecha_reporte'          => now()->subDays(5),
                    'fecha_validacion'       => in_array($rep[9], ['ACTIVO', 'RESUELTO', 'DESCARTADO']) ? now()->subDays(3) : null,
                    'fecha_resolucion'       => $rep[9] === 'RESUELTO' ? now()->subDay() : null,
                    'observacion_validacion' => '[DEMO] Registro validado mediante protocolo de prueba sintética.',
                ]);
            }
        });
    }
}
