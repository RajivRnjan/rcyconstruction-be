<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
        'name', 'site_incharge_id', 'boq_name', 'site_exp', 'np', 'agreement_value', 
        'upto_date_bill_value', 'balance_work_value'
    ];

    public function siteIncharge()
    {
        return $this->belongsTo(SiteIncharge::class, 'site_incharge_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function siteSubcontractors()
    {
        return $this->hasMany(SiteSubcontractor::class);
    }

    public function siteStaff()
    {
        return $this->hasMany(SiteStaff::class);
    }

    public function boqItems()
    {
        return $this->hasMany(BoqItem::class);
    }
}
