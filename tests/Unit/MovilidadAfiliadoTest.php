<?php
namespace Tests\Unit;

use App\Models\{PersonaNoVidente,TokenMovilidad,EventoBaston,User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MovilidadAfiliadoTest extends TestCase
{
    use DatabaseTransactions;
    private function persona(): PersonaNoVidente {
        config(['movilidad.identificacion_prototipo'=>true]);
        $persona=PersonaNoVidente::create(['ide_pnv'=>'TEST-MOV-AFI','cii_pnv'=>'TEST-CI-9427','nom_pnv'=>'MARÍA ELENA',
            'app_pnv'=>'PRUEBA','apm_pnv'=>'EJEMPLO','ape_pnv'=>'PRUEBA EJEMPLO','fen_pnv'=>'1990-01-01',
            'gen_pnv'=>'FEMENINO','gdi_pnv'=>'TOTAL','est_pnv'=>'ACTIVO']);
        DB::table('afiliaciones')->insert(['ide_afi'=>'TEST-MOV-AFI','cod_personas_no_videntes_afi'=>$persona->getKey(),
            'cod_tipos_afiliado_afi'=>DB::table('tipos_afiliado')->value('cod_tipos_afiliado'),'fia_afi'=>today(),'est_afi'=>'ACTIVA']);
        return $persona;
    }
    private function entrar(): string {
        return $this->postJson('/api/movilidad/login',['ci'=>' test-ci-9427 ','nombre'=>'  maría   elena '])->assertOk()->assertJsonPath('nombre','MARÍA ELENA')->json('token');
    }
    public function test_identificacion_no_modifica_padron_y_token_se_revoca(): void {
        $persona=$this->persona();$antes=$persona->fresh()->getAttributes();$usuarios=User::count();$personas=PersonaNoVidente::count();
        $token=$this->entrar();$guardado=TokenMovilidad::latest('id')->firstOrFail();
        $this->assertSame(hash('sha256',$token),$guardado->token_hash);$this->assertNotSame($token,$guardado->token_hash);
        $headers=['Authorization'=>'Bearer '.$token];
        $this->getJson('/api/movilidad/sesion',$headers)->assertOk()->assertJsonPath('nombre','MARÍA ELENA')->assertJsonMissing(['ci'=>'TEST-CI-9427']);
        $this->getJson('/api/movilidad/rutas',$headers)->assertOk();
        $this->assertSame($antes,$persona->fresh()->getAttributes());$this->assertSame($usuarios,User::count());$this->assertSame($personas,PersonaNoVidente::count());
        $this->postJson('/api/movilidad/logout',[],$headers)->assertNoContent();
        $this->getJson('/api/movilidad/sesion',$headers)->assertUnauthorized();
    }
    public function test_datos_incorrectos_inactivos_o_afiliacion_vencida_no_emitir_token(): void {
        $persona=$this->persona();$this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        foreach([['ci'=>'DESCONOCIDO','nombre'=>'MARÍA ELENA'],['ci'=>'TEST-CI-9427','nombre'=>'MARÍA']] as $datos)
            $this->postJson('/api/movilidad/login',$datos)->assertStatus(422)->assertExactJson(['message'=>'No pudimos verificar tus datos. Solicita ayuda al IBC.']);
        $persona->update(['est_pnv'=>'SUSPENDIDO']);
        $this->postJson('/api/movilidad/login',['ci'=>$persona->cii_pnv,'nombre'=>$persona->nom_pnv])->assertStatus(422);
        $persona->update(['est_pnv'=>'ACTIVO']);DB::table('afiliaciones')->where('ide_afi','TEST-MOV-AFI')->update(['fve_afi'=>today()->subDay()]);
        $this->postJson('/api/movilidad/login',['ci'=>$persona->cii_pnv,'nombre'=>$persona->nom_pnv])->assertStatus(422);
        $this->assertSame(0,TokenMovilidad::where('persona_id',$persona->getKey())->count());
    }
    public function test_token_expirado_y_persona_desactivada_exigen_identificacion(): void {
        $persona=$this->persona();$token=$this->entrar();$headers=['Authorization'=>'Bearer '.$token];
        $persona->update(['est_pnv'=>'INACTIVO']);$this->getJson('/api/movilidad/sesion',$headers)->assertUnauthorized();
        $persona->update(['est_pnv'=>'ACTIVO']);TokenMovilidad::where('token_hash',hash('sha256',$token))->update(['expires_at'=>now()->subMinute()]);
        $this->getJson('/api/movilidad/sesion',$headers)->assertUnauthorized();
    }
    public function test_sos_idempotente_sin_gps_y_deteccion_no_crean_obstaculos(): void {
        $persona=$this->persona();$token=$this->entrar();$headers=['Authorization'=>'Bearer '.$token];$obstaculos=DB::table('obstaculos')->count();
        $evento=['evento_uuid'=>(string)Str::uuid(),'tipo'=>'SOS','origen'=>'ESP32','fecha_evento'=>now()->toISOString()];
        $primero=$this->postJson('/api/movilidad/eventos',$evento,$headers)->assertCreated()->assertJsonPath('aviso_familiar_enviado',false)->assertJsonPath('tramo_id',null)->json('evento_id');
        $this->postJson('/api/movilidad/eventos',$evento,$headers)->assertOk()->assertJsonPath('evento_id',$primero);
        $this->assertSame($persona->getKey(),EventoBaston::findOrFail($primero)->persona_id);
        $evento['tipo']='DETECCION';$evento['evento_uuid']=(string)Str::uuid();$evento['distancias_cm']=[12,200,300];
        $this->postJson('/api/movilidad/eventos',$evento,$headers)->assertCreated();
        $this->assertSame($obstaculos,DB::table('obstaculos')->count());
    }
}
