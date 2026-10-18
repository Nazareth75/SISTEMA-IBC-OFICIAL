<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tokens_movilidad',function(Blueprint $t) {
            $t->id();$t->unsignedBigInteger('persona_id');$t->string('token_hash',64)->unique();
            $t->timestamp('expires_at');$t->timestamp('revoked_at')->nullable();$t->timestamps();
            $t->foreign('persona_id')->references('cod_personas_no_videntes')->on('personas_no_videntes');
        });
        Schema::table('eventos_baston',function(Blueprint $t) {
            $t->unsignedBigInteger('usuario_id')->nullable()->change();
            $t->unsignedBigInteger('persona_id')->nullable();
            $t->foreign('persona_id')->references('cod_personas_no_videntes')->on('personas_no_videntes');
            $t->unique(['persona_id','evento_uuid']);
        });
    }
    public function down(): void {
        // No borrar evidencia móvil durante rollback accidental.
        if(\Illuminate\Support\Facades\DB::table('eventos_baston')->whereNotNull('persona_id')->exists())
            throw new RuntimeException('Hay evidencia de afiliados; conservarla antes del rollback.');
        Schema::table('eventos_baston',function(Blueprint $t) {
            $t->dropForeign(['persona_id']);$t->dropUnique(['persona_id','evento_uuid']);$t->dropColumn('persona_id');
        });
        Schema::dropIfExists('tokens_movilidad');
    }
};
