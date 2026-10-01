<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\MasterSheet;
use App\Models\Site;
use App\Models\SiteStaff;
use App\Models\SiteIncharge;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_projects' => MasterSheet::count(),
            'total_sites' => Site::count(),
            'total_staff' => SiteStaff::count(),
            'total_incharge' => SiteIncharge::count()
        ]);
    }
}
