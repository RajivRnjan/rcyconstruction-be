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
        Schema::table('material_outs', function (Blueprint $table) {
            $table->string('type')->default('used')->after('date'); // 'used' or 'transfer'
            $table->foreignId('to_site_id')->nullable()->after('type')->constrained('sites')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_outs', function (Blueprint $table) {
            $table->dropForeign(['to_site_id']);
            $table->dropColumn(['type', 'to_site_id']);
        });
    }
};
