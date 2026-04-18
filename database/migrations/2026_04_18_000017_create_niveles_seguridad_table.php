<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('niveles_seguridad', function (Blueprint $table) {
            $table->id('cod_niveles_seguridad');
            $table->string('nom_nse', 50)->unique();
            $table->integer('niv_nse')->unique();
            $table->string('des_nse', 200)->nullable();
            $table->string('col_nse', 7)->default('#000000');
            $table->timestamp('fcr_nse')->useCurrent();
            $table->timestamp('fmo_nse')->useCurrent()->useCurrentOnUpdate();
        });

        DB::table('niveles_seguridad')->insert([
            ['nom_nse' => 'CRÍTICO', 'niv_nse' => 1, 'des_nse' => 'Ruta no apta para navegación autónoma.', 'col_nse' => '#E24B4A'],
            ['nom_nse' => 'BAJO', 'niv_nse' => 2, 'des_nse' => 'Ruta con alto riesgo, requiere acompañante.', 'col_nse' => '#EF9F27'],
            ['nom_nse' => 'MEDIO', 'niv_nse' => 3, 'des_nse' => 'Ruta con riesgos manejables con precaución.', 'col_nse' => '#FAC775'],
            ['nom_nse' => 'ALTO', 'niv_nse' => 4, 'des_nse' => 'Ruta segura con mínimas precauciones.', 'col_nse' => '#97C459'],
            ['nom_nse' => 'ÓPTIMO', 'niv_nse' => 5, 'des_nse' => 'Ruta totalmente segura y accesible de forma autónoma.', 'col_nse' => '#1D9E75'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('niveles_seguridad');
    }
};
