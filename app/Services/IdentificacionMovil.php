<?php
namespace App\Services;

use App\Models\PersonaNoVidente;

/** Identificación de prototipo: solo permite el acceso a afiliados habilitados en el sistema. */
class IdentificacionMovil
{
    public static function nombre(string $valor): string {
        return mb_strtoupper(preg_replace('/\s+/u', ' ', trim($valor)), 'UTF-8');
    }
    public static function ci(string $valor): string {
        return mb_strtoupper(preg_replace('/\s+/u', '', $valor), 'UTF-8');
    }
    public function habilitada(PersonaNoVidente $persona): bool {
        return $persona->est_pnv === 'ACTIVO' && !$persona->fbj_pnv
            && $persona->afiliaciones()->where('est_afi','ACTIVA')->whereNull('fbj_afi')
                ->whereDate('fia_afi','<=',today())->where(fn($q)=>$q->whereNull('fve_afi')->orWhereDate('fve_afi','>=',today()))->exists();
    }
    public function verificar(string $ci, ?string $nombre = null): ?PersonaNoVidente {
        $candidatas=PersonaNoVidente::whereNull('fbj_pnv')
            ->whereRaw("REPLACE(UPPER(TRIM(cii_pnv)), ' ', '') = ?", [self::ci($ci)])->limit(2)->get();
        if($candidatas->count() !== 1) return null;
        $persona=$candidatas->first();
        if ($nombre === null) return $this->habilitada($persona) ? $persona : null;
        $n=self::nombre($nombre);
        return $this->habilitada($persona) && in_array($n,[self::nombre($persona->nom_pnv),self::nombre($persona->nombre_completo)],true)
            ? $persona : null;
    }
}
