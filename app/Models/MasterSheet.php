<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSheet extends Model
{
    protected $fillable = [
        'project_name',
        'boq_name',
        'site_exp',
        'np',
        'agreement_value',
        'upto_date_bill_value',
        'balance_work_value'
    ];

    public function boqItems()
    {
        return $this->hasMany(BoqItem::class);
    }
}
