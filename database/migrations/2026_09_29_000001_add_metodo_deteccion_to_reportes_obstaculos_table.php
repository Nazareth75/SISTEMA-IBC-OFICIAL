<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reportes_obstaculos', function (Blueprint $table) {
            $table->string('metodo_deteccion')->default('MANUAL')->after('nivel_riesgo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reportes_obstaculos', function (Blueprint $table) {
            $table->dropColumn('metodo_deteccion');
        });
    }
};
