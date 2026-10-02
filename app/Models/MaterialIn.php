<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialIn extends Model
{
    protected $fillable = ['unit', 
        'date',
        'site_id',
        'transferred_from_site_id',
        'supplier_id',
        'material_id',
        'qnty',
        'rate',
        'amount',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function transferredFromSite()
    {
        return $this->belongsTo(Site::class, 'transferred_from_site_id');
    }
}

