<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'date', 'site_incharge', 'outstanding_balance'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function staff()
    {
        return $this->hasMany(DailyReportStaff::class);
    }

    public function expenses()
    {
        return $this->hasMany(DailyReportExpense::class);
    }

    public function subcontractors()
    {
        return $this->hasMany(DailyReportSubcontractor::class);
    }
}