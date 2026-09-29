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
            $table->unsignedBigInteger('cod_obstaculos_rpo')->nullable()->after('validado_por');
            $table->foreign('cod_obstaculos_rpo')->references('cod_obstaculos')->on('obstaculos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reportes_obstaculos', function (Blueprint $table) {
            $table->dropForeign(['cod_obstaculos_rpo']);
            $table->dropColumn('cod_obstaculos_rpo');
        });
    }
};
