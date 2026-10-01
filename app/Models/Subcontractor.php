<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcontractor extends Model
{
    protected $fillable = [
        'date',
        'site_id',
        'name',
        'amount',
        'work_details',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}
