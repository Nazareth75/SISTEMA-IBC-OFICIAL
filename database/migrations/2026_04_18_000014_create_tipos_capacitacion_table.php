<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tipos_capacitacion', function (Blueprint $table) {
            $table->id('cod_tipos_capacitacion');
            $table->string('nom_tca', 100)->unique();
            $table->string('des_tca', 250)->nullable();
            $table->boolean('act_tca')->default(true);
            $table->timestamp('fcr_tca')->useCurrent();
            $table->timestamp('fmo_tca')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fbj_tca')->nullable();
        });

        DB::table('tipos_capacitacion')->insert([
            ['nom_tca' => 'ORIENTACIÓN Y MOVILIDAD', 'des_tca' => 'Técnicas de desplazamiento autónomo y seguro.'],
            ['nom_tca' => 'BRAILLE', 'des_tca' => 'Lectura y escritura en sistema Braille.'],
            ['nom_tca' => 'TECNOLOGÍA ASISTIVA', 'des_tca' => 'Uso de herramientas tecnológicas para personas con discapacidad visual.'],
            ['nom_tca' => 'HABILIDADES DE VIDA', 'des_tca' => 'Actividades de la vida diaria de forma autónoma.'],
            ['nom_tca' => 'CAPACITACIÓN LABORAL', 'des_tca' => 'Formación para inserción laboral.'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_capacitacion');
    }
};
