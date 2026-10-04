<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeadOfficeExpense extends Model
{
    protected $fillable = [
        'date',
        'site_id',
        'expenses_head',
        'supplier_id',
        'subcontractor_id',
        'staff_id',
        'person_name',
        'mode_of_payment',
        'account_id',
        'amount',
        'remark',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
