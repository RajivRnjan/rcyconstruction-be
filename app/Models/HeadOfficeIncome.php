<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeadOfficeIncome extends Model
{
    protected $fillable = [
        'date',
        'project_id',
        'client_name',
        'mode_of_payment',
        'account_id',
        'amount',
        'remarks',
    ];

    public function project()
    {
        return $this->belongsTo(MasterSheet::class, 'project_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
