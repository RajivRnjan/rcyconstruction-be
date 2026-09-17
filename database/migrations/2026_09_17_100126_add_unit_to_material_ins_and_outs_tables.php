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
        Schema::table('material_ins', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('material_id');
        });

        Schema::table('material_outs', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_ins', function (Blueprint $table) {
            $table->dropColumn('unit');
        });

        Schema::table('material_outs', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
