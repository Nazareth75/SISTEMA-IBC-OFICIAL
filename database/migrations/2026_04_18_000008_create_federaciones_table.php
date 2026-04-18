<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('federaciones', function (Blueprint $table) {
            $table->id('cod_federaciones');
            $table->string('nom_fed', 150)->unique();
            $table->string('sig_fed', 20)->nullable();
            $table->string('des_fed', 250)->nullable();
            $table->string('pai_fed', 100)->default('BOLIVIA');
            $table->string('ciu_fed', 100)->nullable();
            $table->string('tel_fed', 20)->nullable();
            $table->string('cor_fed', 150)->nullable();
            $table->boolean('act_fed')->default(true);
            $table->timestamp('fcr_fed')->useCurrent();
            $table->timestamp('fmo_fed')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_fed')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('federaciones');
    }
};
