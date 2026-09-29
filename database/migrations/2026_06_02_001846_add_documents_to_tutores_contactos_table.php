<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tutores_contactos', function (Blueprint $table) {
            $table->string('doc_ci_tco', 255)->nullable()->after('etl_tco');
            $table->string('doc_legal_tco', 255)->nullable()->after('doc_ci_tco');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutores_contactos', function (Blueprint $table) {
            $table->dropColumn(['doc_ci_tco', 'doc_legal_tco']);
        });
    }
};
