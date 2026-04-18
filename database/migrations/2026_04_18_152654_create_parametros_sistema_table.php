<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parametros_sistema', function (Blueprint $table) {
            $table->id('cod_parametros_sistema');
            $table->string('cla_psi', 100)->unique();
            $table->string('val_psi', 255);
            $table->string('des_psi', 300)->nullable();
            $table->enum('tip_psi', ['TEXTO','NUMERO','FECHA','BOOLEANO'])->default('TEXTO');
            $table->boolean('edi_psi')->default(true);
            $table->timestamp('fcr_psi')->useCurrent();
            $table->timestamp('fmo_psi')->useCurrent()->useCurrentOnUpdate();
        });

        DB::table('parametros_sistema')->insert([
            ['cla_psi' => 'nombre_sistema', 'val_psi' => 'SISTEMA ADMINISTRATIVO IBC', 'des_psi' => 'Nombre del sistema', 'tip_psi' => 'TEXTO', 'edi_psi' => 0],
            ['cla_psi' => 'nombre_institucion', 'val_psi' => 'INSTITUTO BOLIVIANO DE LA CEGUERA', 'des_psi' => 'Nombre oficial de la institución', 'tip_psi' => 'TEXTO', 'edi_psi' => 0],
            ['cla_psi' => 'sigla_institucion', 'val_psi' => 'IBC', 'des_psi' => 'Sigla de la institución', 'tip_psi' => 'TEXTO', 'edi_psi' => 0],
            ['cla_psi' => 'tipo_cambio_usd', 'val_psi' => '6.97', 'des_psi' => 'Tipo de cambio USD a BOB', 'tip_psi' => 'NUMERO', 'edi_psi' => 1],
            ['cla_psi' => 'umbral_seguridad_ruta', 'val_psi' => '3', 'des_psi' => 'Nivel mínimo de seguridad para publicar', 'tip_psi' => 'NUMERO', 'edi_psi' => 1],
            ['cla_psi' => 'max_obstaculos_tramo', 'val_psi' => '5', 'des_psi' => 'Máx. obstáculos activos antes de alerta', 'tip_psi' => 'NUMERO', 'edi_psi' => 1],
            ['cla_psi' => 'ciudad_principal', 'val_psi' => 'LA PAZ', 'des_psi' => 'Ciudad principal de operaciones', 'tip_psi' => 'TEXTO', 'edi_psi' => 1],
            ['cla_psi' => 'pais', 'val_psi' => 'BOLIVIA', 'des_psi' => 'País de operaciones', 'tip_psi' => 'TEXTO', 'edi_psi' => 0],
            ['cla_psi' => 'version_sistema', 'val_psi' => '1.0.0', 'des_psi' => 'Versión actual del sistema', 'tip_psi' => 'TEXTO', 'edi_psi' => 0],
            ['cla_psi' => 'email_soporte', 'val_psi' => 'soporte@ibc.bo', 'des_psi' => 'Correo de soporte técnico', 'tip_psi' => 'TEXTO', 'edi_psi' => 1],
            ['cla_psi' => 'max_intentos_login', 'val_psi' => '5', 'des_psi' => 'Intentos fallidos antes de bloquear', 'tip_psi' => 'NUMERO', 'edi_psi' => 1],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('parametros_sistema');
    }
};

