<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('beneficios_sociales', function (Blueprint $table) {
            $table->id('cod_beneficios_sociales');
            $table->string('nom_bso', 150);
            $table->enum('tip_bso', ['ECONÓMICO','MATERIAL','SERVICIO','EDUCATIVO','OTRO']);
            $table->string('des_bso', 250)->nullable();
            $table->boolean('act_bso')->default(true);
            $table->timestamp('fcr_bso')->useCurrent();
            $table->timestamp('fmo_bso')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_bso')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficios_sociales');
    }
};
