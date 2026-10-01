<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('head_office_expenses', function (Blueprint $table) {
            // Drop foreign key and make nullable
            $table->dropForeign(['supplier_id']);
            $table->unsignedBigInteger('supplier_id')->nullable()->change();

            // Add new nullable relation columns
            $table->foreignId('subcontractor_id')->nullable()->constrained('subcontractors')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('site_staff')->nullOnDelete();
            $table->string('person_name')->nullable(); // For manually typed names
        });
        
        // Re-add the supplier foreign key with nullOnDelete or cascade since it's nullable now
        Schema::table('head_office_expenses', function (Blueprint $table) {
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('head_office_expenses', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['subcontractor_id']);
            $table->dropForeign(['staff_id']);
            
            $table->dropColumn(['subcontractor_id', 'staff_id', 'person_name']);
        });
        
        Schema::table('head_office_expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable(false)->change();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
        });
    }
};
