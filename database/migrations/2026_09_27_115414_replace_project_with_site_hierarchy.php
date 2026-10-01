<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop project_id from sites
        Schema::table('sites', function (Blueprint $table) {
            // $table->dropForeign(['project_id']); // Ignored if not exists
            // $table->dropColumn('project_id');
        });

        // 2. Rename project_id to site_id in other tables
        $tables = ['head_office_incomes', 'site_incharges', 'head_office_expenses', 'material_outs', 'subcontractors'];
        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) use ($t) {
                $table->dropForeign([ 'project_id' ]);
            });
            Schema::table($t, function (Blueprint $table) use ($t) {
                $table->renameColumn('project_id', 'site_id');
            });
            Schema::table($t, function (Blueprint $table) use ($t) {
                $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            });
        }

        // 3. Drop master_sheets table
        
        // Drop foreign key from boq_items before dropping master_sheets
        Schema::table('boq_items', function (Blueprint $table) {
            $table->dropForeign(['master_sheet_id']);
        });
        
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('master_sheets');
        Schema::enableForeignKeyConstraints();


    }

    public function down(): void
    {
        // Down migration omitted for brevity
    }
};
