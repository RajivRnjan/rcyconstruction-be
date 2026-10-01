<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SiteSubcontractor extends Model {
    protected $fillable = ['site_id', 'name'];
    public function site() {
        return $this->belongsTo(Site::class);
    }
}
