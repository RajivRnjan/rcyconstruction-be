<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcontractor extends Model
{
    protected $fillable = [
        'date',
        'project_id',
        'name',
        'no_of_labour',
        'work_details',
    ];

    public function project()
    {
        return $this->belongsTo(MasterSheet::class, 'project_id');
    }
}
