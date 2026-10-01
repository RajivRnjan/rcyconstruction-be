<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialOut extends Model
{
    protected $fillable = ['unit', 
        'date',
        'site_id',
        'expenses_head_id',
        'supplier_id',
        'material_id',
        'qnty',
        'rate',
        'amount',
        'paid',
        'balance',
        'remark'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function expensesHead()
    {
        return $this->belongsTo(ExpensesHead::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
