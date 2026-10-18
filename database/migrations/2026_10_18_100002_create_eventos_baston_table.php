<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('eventos_baston', function(Blueprint $t) {
            $t->id(); $t->uuid('evento_uuid'); $t->unsignedBigInteger('usuario_id');
            $t->string('tipo'); $t->string('origen'); $t->string('estado')->default('REGISTRADO');
            $t->decimal('latitud',10,7)->nullable(); $t->decimal('longitud',11,7)->nullable();
            $t->float('precision_m')->nullable(); $t->string('fuente_ubicacion')->nullable();
            $t->timestamp('fecha_evento'); $t->unsignedBigInteger('tramo_id')->nullable();
            $t->json('datos'); $t->timestamps(); $t->unique(['usuario_id','evento_uuid']);
            $t->foreign('usuario_id')->references('cod_usuarios')->on('usuarios');
            $t->foreign('tramo_id')->references('cod_calles_tramos')->on('calles_tramos');
        });
    }
    public function down(): void {Schema::dropIfExists('eventos_baston');}
};
