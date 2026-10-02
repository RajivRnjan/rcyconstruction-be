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
            $table->foreignId('transferred_from_site_id')->nullable()->after('site_id')->constrained('sites')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('material_ins', function (Blueprint $table) {
            $table->dropForeign(['transferred_from_site_id']);
            $table->dropColumn('transferred_from_site_id');
        });
    }
};
