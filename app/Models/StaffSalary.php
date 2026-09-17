<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'name',
        'salary',
        'prv_month_due_adv',
        'total_working_day',
        'current_month_salary',
        'debit_amount',
        'balance',
        'remark',
    ];
}
