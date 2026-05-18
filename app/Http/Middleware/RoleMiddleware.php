<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$rolesPermitidos): Response
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        if ($usuario->est_usu !== 'ACTIVO') {
            auth()->logout();

            return redirect()->route('login')->with('error', 'TU CUENTA NO ESTÁ ACTIVA.');
        }

        $rolesPermitidos = array_values(array_filter(array_map(
            static fn (string $rol): string => strtoupper(trim($rol)),
            array_merge(...array_map(
                static fn (string $rol): array => explode(',', $rol),
                $rolesPermitidos
            ))
        )));

        $rolUsuario = strtoupper(trim((string) ($usuario->role?->nom_rol ?? '')));

        if ($rolUsuario === '' || ! in_array($rolUsuario, $rolesPermitidos, true)) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
