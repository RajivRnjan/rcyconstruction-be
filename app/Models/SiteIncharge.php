<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteIncharge extends Model
{
    protected $fillable = [
        'date',
        'site_id',
        'account_id',
        'name',
        'opening_bal',
        'credit',
        'debit_account',
        'exp',
        'balance',
        'remark',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
