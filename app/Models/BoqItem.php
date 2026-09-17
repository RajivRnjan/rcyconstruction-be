<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoqItem extends Model
{
    protected $fillable = [
        'master_sheet_id',
        'item_name',
        'est_qnt',
        'unit',
        'rate',
        'work_done_qty'
    ];

    public function masterSheet()
    {
        return $this->belongsTo(MasterSheet::class);
    }
}
