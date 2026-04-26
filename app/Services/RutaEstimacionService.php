<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RutaEstimacionService
{
    /**
     * Calcula una estimación base (distancia y tiempo) para recorrido peatonal.
     * Devuelve null cuando no se logra resolver origen/destino o la API externa falla.
     */
    public function estimar(string $origen, string $destino): ?array
    {
        $origenCoords = $this->geocodificar($origen);
        $destinoCoords = $this->geocodificar($destino);

        if (!$origenCoords || !$destinoCoords) {
            return null;
        }

        return $this->estimarDesdePuntos($origenCoords, $destinoCoords);
    }

    public function estimarPorCoordenadas(
        float $origenLat,
        float $origenLng,
        float $destinoLat,
        float $destinoLng
    ): ?array {
        $origenCoords = [
            'lat' => $origenLat,
            'lon' => $origenLng,
            'display_name' => sprintf('PUNTO %.6f, %.6f', $origenLat, $origenLng),
        ];

        $destinoCoords = [
            'lat' => $destinoLat,
            'lon' => $destinoLng,
            'display_name' => sprintf('PUNTO %.6f, %.6f', $destinoLat, $destinoLng),
        ];

        return $this->estimarDesdePuntos($origenCoords, $destinoCoords);
    }

    private function estimarDesdePuntos(array $origenCoords, array $destinoCoords): ?array
    {
        if (!isset($origenCoords['lat'], $origenCoords['lon'], $destinoCoords['lat'], $destinoCoords['lon'])) {
            return null;
        }

        $osrmUrl = rtrim((string) config('services.osrm.url', 'https://router.project-osrm.org'), '/');

        $origenSnapeado = $this->snapToRoad($osrmUrl, (float) $origenCoords['lat'], (float) $origenCoords['lon']);
        $destinoSnapeado = $this->snapToRoad($osrmUrl, (float) $destinoCoords['lat'], (float) $destinoCoords['lon']);

        if (!$origenSnapeado || !$destinoSnapeado) {
            return null;
        }

        $profiles = ['foot', 'driving'];
        $response = null;

        foreach ($profiles as $profile) {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get(sprintf(
                    '%s/route/v1/%s/%s,%s;%s,%s',
                    $osrmUrl,
                    $profile,
                    $origenSnapeado['lon'],
                    $origenSnapeado['lat'],
                    $destinoSnapeado['lon'],
                    $destinoSnapeado['lat']
                ), [
                    'overview' => 'full',
                    'geometries' => 'geojson',
                    'alternatives' => 'false',
                    'steps' => 'false',
                    'continue_straight' => 'true',
                ]);

            if ($response->successful() && data_get($response->json(), 'routes.0.distance') !== null) {
                break;
            }

            $response = null;
        }

        if (!$response) {
            $response = $this->intentarMapMatching($osrmUrl, $origenSnapeado, $destinoSnapeado);
        }

        if (!$response) {
            return null;
        }

        $json = $response->json();
        $ruta = data_get($json, 'routes.0') ?? data_get($json, 'matchings.0');

        if (!$ruta || !isset($ruta['distance'], $ruta['duration'])) {
            return null;
        }

        $coordenadas = collect(data_get($ruta, 'geometry.coordinates', []))
            ->filter(fn ($point) => is_array($point) && count($point) >= 2)
            ->map(fn ($point) => [
                'lat' => (float) $point[1],
                'lng' => (float) $point[0],
            ])
            ->values()
            ->all();

        $distanciaMetros = round((float) $ruta['distance'], 2);
        $tiempoMinutos = max(1, (int) ceil(((float) $ruta['duration']) / 60));

        return [
            'distancia_metros' => $distanciaMetros,
            'tiempo_minutos' => $tiempoMinutos,
            'origen_resuelto' => $origenCoords['display_name'],
            'destino_resuelto' => $destinoCoords['display_name'],
            'perfil_usado' => data_get($json, 'routes.0') ? 'ROUTE' : 'MATCH',
            'origen_coords' => [
                'lat' => (float) $origenCoords['lat'],
                'lng' => (float) $origenCoords['lon'],
            ],
            'destino_coords' => [
                'lat' => (float) $destinoCoords['lat'],
                'lng' => (float) $destinoCoords['lon'],
            ],
            'trayecto_coords' => $coordenadas,
        ];
    }

    private function snapToRoad(string $osrmUrl, float $lat, float $lng): ?array
    {
        $response = Http::timeout(8)
            ->acceptJson()
            ->get(sprintf('%s/nearest/v1/foot/%s,%s', $osrmUrl, $lng, $lat), [
                'number' => 1,
            ]);

        if (!$response->successful()) {
            return [
                'lat' => $lat,
                'lon' => $lng,
            ];
        }

        $waypoint = data_get($response->json(), 'waypoints.0.location');

        if (!is_array($waypoint) || count($waypoint) < 2) {
            return [
                'lat' => $lat,
                'lon' => $lng,
            ];
        }

        return [
            'lat' => (float) $waypoint[1],
            'lon' => (float) $waypoint[0],
        ];
    }

    private function intentarMapMatching(string $osrmUrl, array $origen, array $destino): ?\Illuminate\Http\Client\Response
    {
        $coords = [];
        $totalPuntos = 12;

        for ($i = 0; $i < $totalPuntos; $i++) {
            $t = $totalPuntos === 1 ? 0 : $i / ($totalPuntos - 1);

            $coords[] = sprintf(
                '%s,%s',
                $origen['lon'] + (($destino['lon'] - $origen['lon']) * $t),
                $origen['lat'] + (($destino['lat'] - $origen['lat']) * $t)
            );
        }

        $timestamps = implode(';', array_map(fn ($index) => $index * 10, array_keys($coords)));
        $radiuses = implode(';', array_fill(0, count($coords), '80'));

        $response = Http::timeout(12)
            ->acceptJson()
            ->get(sprintf(
                '%s/match/v1/foot/%s',
                $osrmUrl,
                implode(';', $coords)
            ), [
                'steps' => 'false',
                'geometries' => 'geojson',
                'overview' => 'full',
                'tidy' => 'true',
                'timestamps' => $timestamps,
                'radiuses' => $radiuses,
            ]);

        if (!$response->successful()) {
            return null;
        }

        if (data_get($response->json(), 'code') !== 'Ok') {
            return null;
        }

        if (data_get($response->json(), 'matchings.0.distance') === null) {
            return null;
        }

        return $response;
    }

    private function geocodificar(string $termino): ?array
    {
        $nominatimUrl = rtrim((string) config('services.nominatim.url', 'https://nominatim.openstreetmap.org'), '/');
        $agente = (string) config('services.nominatim.user_agent', config('app.name', 'SISTEMA-IBC') . '/1.0');

        $response = Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => $agente,
            ])
            ->get($nominatimUrl . '/search', [
                'q' => $termino . ', BOLIVIA',
                'format' => 'jsonv2',
                'limit' => 1,
                'addressdetails' => 0,
            ]);

        if (!$response->successful()) {
            return null;
        }

        $resultado = data_get($response->json(), '0');

        if (!$resultado || !isset($resultado['lat'], $resultado['lon'])) {
            return null;
        }

        return [
            'lat' => (float) $resultado['lat'],
            'lon' => (float) $resultado['lon'],
            'display_name' => (string) ($resultado['display_name'] ?? ''),
        ];
    }

    public function buscarPuntosInteres(string $termino): array
    {
        $termino = trim($termino);

        if ($termino === '') {
            return [];
        }

        $nominatimUrl = rtrim((string) config('services.nominatim.url', 'https://nominatim.openstreetmap.org'), '/');
        $agente = (string) config('services.nominatim.user_agent', config('app.name', 'SISTEMA-IBC') . '/1.0');

        $response = Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => $agente,
            ])
            ->get($nominatimUrl . '/search', [
                'q' => $termino . ', BOLIVIA',
                'format' => 'jsonv2',
                'limit' => 6,
                'addressdetails' => 1,
                'extratags' => 1,
            ]);

        if (!$response->successful()) {
            return [];
        }

        return collect($response->json())
            ->filter(fn ($item) => is_array($item) && isset($item['lat'], $item['lon'], $item['display_name']))
            ->map(fn ($item) => [
                'nombre' => (string) ($item['display_name'] ?? ''),
                'lat' => (float) $item['lat'],
                'lng' => (float) $item['lon'],
                'tipo' => (string) data_get($item, 'type', ''),
                'categoria' => (string) data_get($item, 'class', ''),
            ])
            ->values()
            ->all();
    }

    private function reverseGeocodificar(float $lat, float $lng): ?string
    {
        $nominatimUrl = rtrim((string) config('services.nominatim.url', 'https://nominatim.openstreetmap.org'), '/');
        $agente = (string) config('services.nominatim.user_agent', config('app.name', 'SISTEMA-IBC') . '/1.0');

        $response = Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => $agente,
            ])
            ->get($nominatimUrl . '/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'jsonv2',
                'addressdetails' => 0,
            ]);

        if (!$response->successful()) {
            return null;
        }

        $display = (string) data_get($response->json(), 'display_name', '');

        return $display !== '' ? strtoupper($display) : null;
    }
}