<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierTransaction extends Model
{
    protected $fillable = [
        'supplier_id',
        'date',
        'invoice_no',
        'outstanding_balance',
        'payment',
        'invoice_amount',
        'balance'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
