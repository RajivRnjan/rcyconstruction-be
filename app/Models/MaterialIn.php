<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialIn extends Model
{
    protected $fillable = [
        'date',
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
}
