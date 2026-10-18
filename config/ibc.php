<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Umbral mínimo de verificaciones para consenso colaborativo
    |--------------------------------------------------------------------------
    | Define la cantidad mínima de voluntarios únicos independientes requeridos
    | que reporten "YA_NO_ESTA" (sin conflicto reciente de "SIGUE_PRESENTE")
    | para catalogar el obstáculo como CANDIDATO A RESOLUCIÓN ante el técnico.
    */
    'min_verificaciones_resolucion' => (int) env('IBC_MIN_VERIFICACIONES_RESOLUCION', 2),
];
