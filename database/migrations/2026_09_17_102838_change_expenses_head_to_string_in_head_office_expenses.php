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
        Schema::table('head_office_expenses', function (Blueprint $table) {
            $table->dropForeign(['expenses_head_id']);
            $table->dropColumn('expenses_head_id');
            $table->string('expenses_head')->nullable()->after('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('head_office_expenses', function (Blueprint $table) {
            $table->dropColumn('expenses_head');
            $table->foreignId('expenses_head_id')->nullable()->constrained('expenses_heads')->onDelete('cascade');
        });
    }
};
