<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\SiteIncharge;
use App\Models\DailyReport;
use App\Models\StaffSalary;
use App\Models\Account;
use App\Models\Supplier;
use App\Models\Subcontractor;
use App\Models\Material;
use App\Models\HeadOfficeIncome;
use App\Models\HeadOfficeExpense;
use App\Models\DailyReportExpense;

class DashboardController extends Controller
{
    public function stats()
    {
        // Aggregate statistics for the dashboard based on developed modules
        
        $totalSites = Site::count();
        $totalSiteIncharge = SiteIncharge::count();
        $totalDailyReports = DailyReport::count();
        // Since Staff salaries table holds names, let's count unique names as staff count, or just total rows
        $totalStaff = StaffSalary::distinct('name')->count('name');
        
        $totalAccounts = Account::count();
        $totalSuppliers = Supplier::count();
        $totalSubcontractors = Subcontractor::count();
        $totalMaterials = Material::count();
        
        // Financials (can be extended further)
        $hoIncome = HeadOfficeIncome::sum('amount');
        $hoExpense = HeadOfficeExpense::sum('amount');
        
        $supplierPayments = DailyReportExpense::whereIn('type', ['SUPPLIER PAYMENT', 'PARTY PAYMENT'])->sum('amount');
        $totalSalaryPaid = StaffSalary::sum('salary');

        // Recent activity (e.g., last 5 daily reports)
        $recentReports = DailyReport::with('site')->orderBy('date', 'desc')->take(5)->get();

        return response()->json([
            'stats' => [
                'total_sites' => $totalSites,
                'total_incharge' => $totalSiteIncharge,
                'total_reports' => $totalDailyReports,
                'total_staff' => $totalStaff,
                'total_accounts' => $totalAccounts,
                'total_suppliers' => $totalSuppliers,
                'total_subcontractors' => $totalSubcontractors,
                'total_materials' => $totalMaterials,
            ],
            'financials' => [
                'ho_income' => $hoIncome,
                'ho_expense' => $hoExpense,
                'supplier_payments' => $supplierPayments,
                'total_salary_paid' => $totalSalaryPaid,
            ],
            'recent_reports' => $recentReports
        ]);
    }
}
