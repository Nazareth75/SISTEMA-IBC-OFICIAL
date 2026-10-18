<?php
return [
    'identificacion_prototipo' => (bool) env('MOVILIDAD_IDENTIFICACION_PROTOTIPO',false),
    'permitir_demo' => (bool) env('MOVILIDAD_PERMITIR_DEMO',false),
    'tolerancia_gps_m' => (float) env('MOVILIDAD_TOLERANCIA_GPS_M',20),
    // Escenario reproducible: mayor exploración, sin cambiar la función de costo.
    'aco_demo_la_paz' => ['beta'=>1.0,'ants'=>60,'iterations'=>100,'seed'=>42],
];
