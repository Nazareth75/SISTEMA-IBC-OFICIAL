<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('experiencias_usuario', function (Blueprint $table) {
            $table->id('cod_experiencias_usuario');
            $table->unsignedBigInteger('cod_recorridos_realizados_exu')->unique();
            $table->unsignedTinyInteger('cal_exu');
            $table->text('com_exu')->nullable();
            $table->string('dif_exu', 300)->nullable();
            $table->boolean('rec_exu')->default(true);
            $table->timestamp('fcr_exu')->useCurrent();
            $table->timestamp('fmo_exu')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('cod_recorridos_realizados_exu')->references('cod_recorridos_realizados')->on('recorridos_realizados');
        });

        DB::statement('ALTER TABLE experiencias_usuario ADD CONSTRAINT chk_cal_exu CHECK (cal_exu BETWEEN 1 AND 5)');
    }

    public function down(): void
    {
        Schema::dropIfExists('experiencias_usuario');
    }
};
