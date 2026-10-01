<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_incharges', function (Blueprint $table) {
            $table->unsignedBigInteger('site_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('site_incharges', function (Blueprint $table) {
            $table->unsignedBigInteger('site_id')->nullable(false)->change();
        });
    }
};
