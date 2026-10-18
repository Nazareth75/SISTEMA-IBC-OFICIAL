<?php
namespace App\Http\Middleware;

use App\Models\TokenMovilidad;
use App\Services\IdentificacionMovil;
use Closure;
use Illuminate\Http\Request;

class AutenticarMovilidad
{
    public function handle(Request $request, Closure $next) {
        $bearer=$request->bearerToken();
        abort_unless(is_string($bearer) && strlen($bearer)===64,401,'Identificación requerida.');
        $token=TokenMovilidad::where('token_hash',hash('sha256',$bearer))->whereNull('revoked_at')->where('expires_at','>',now())->first();
        abort_unless($token && $token->persona && app(IdentificacionMovil::class)->habilitada($token->persona),401,'Identificación requerida.');
        $request->attributes->set('tokenMovilidad',$token);
        $request->attributes->set('afiliadoMovil',$token->persona);
        return $next($request);
    }
}
