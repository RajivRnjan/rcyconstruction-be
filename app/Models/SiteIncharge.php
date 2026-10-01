<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteIncharge extends Model
{
    protected $fillable = [
        'date',
        'site_id',
        'name',
        'opening_bal',
        'credit',
        'debit_account',
        'exp',
        'balance',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}
