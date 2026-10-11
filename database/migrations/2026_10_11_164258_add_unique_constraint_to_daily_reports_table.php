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
        // 1. Clean up orphaned daily report child records
        \Illuminate\Support\Facades\DB::table('daily_report_subcontractors')
            ->whereNotIn('daily_report_id', function ($query) {
                $query->select('id')->from('daily_reports');
            })->delete();

        \Illuminate\Support\Facades\DB::table('daily_report_expenses')
            ->whereNotIn('daily_report_id', function ($query) {
                $query->select('id')->from('daily_reports');
            })->delete();

        \Illuminate\Support\Facades\DB::table('daily_report_staff')
            ->whereNotIn('daily_report_id', function ($query) {
                $query->select('id')->from('daily_reports');
            })->delete();

        // 2. Remove duplicate daily_reports keeping the highest id
        $duplicates = \Illuminate\Support\Facades\DB::table('daily_reports')
            ->select('site_id', 'date', \Illuminate\Support\Facades\DB::raw('MAX(id) as keep_id'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as cnt'))
            ->groupBy('site_id', 'date')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            \Illuminate\Support\Facades\DB::table('daily_reports')
                ->where('site_id', $dup->site_id)
                ->where('date', $dup->date)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        // 3. Add unique constraint on (site_id, date)
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->unique(['site_id', 'date'], 'daily_reports_site_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropUnique('daily_reports_site_date_unique');
        });
    }
};
