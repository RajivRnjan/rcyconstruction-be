<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReportExpense extends Model
{
    use HasFactory;

    protected $fillable = ['daily_report_id', 'type', 'name', 'amount'];
}