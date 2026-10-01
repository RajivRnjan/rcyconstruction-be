<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReportSubcontractor extends Model
{
    use HasFactory;

    protected $fillable = ['daily_report_id', 'name', 'no_of_labour', 'amount', 'work_details'];

    public function report()
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }
}