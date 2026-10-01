<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReportStaff extends Model
{
    use HasFactory;

    protected $fillable = ['daily_report_id', 'staff_id', 'name', 'status'];
}