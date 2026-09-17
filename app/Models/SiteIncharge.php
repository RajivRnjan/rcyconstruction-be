<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteIncharge extends Model
{
    protected $fillable = [
        'date',
        'project_id',
        'name',
        'opening_bal',
        'credit',
        'debit_account',
        'exp',
        'balance',
    ];

    public function project()
    {
        return $this->belongsTo(MasterSheet::class, 'project_id');
    }
}
