<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tipos_afiliado', function (Blueprint $table) {
            $table->id('cod_tipos_afiliado');
            $table->string('nom_taf', 100)->unique();
            $table->string('des_taf', 250)->nullable();
            $table->boolean('act_taf')->default(true);
            $table->timestamp('fcr_taf')->useCurrent();
            $table->timestamp('fmo_taf')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_taf')->nullable();
        });

        DB::table('tipos_afiliado')->insert([
            ['nom_taf' => 'AFILIADO REGULAR', 'des_taf' => 'Persona registrada formalmente en el IBC con todos sus derechos.'],
            ['nom_taf' => 'BENEFICIARIO', 'des_taf' => 'Recibe beneficios sociales directos gestionados por el IBC.'],
            ['nom_taf' => 'AFILIADO EXTERNO', 'des_taf' => 'Vinculado al IBC a través de una federación externa.'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_afiliado');
    }
};
