<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteStaff extends Model
{
    protected $fillable = ['site_id', 'name', 'salary'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
