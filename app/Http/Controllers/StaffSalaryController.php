<?php

namespace App\Http\Controllers;

use App\Models\StaffSalary;
use Illuminate\Http\Request;

class StaffSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Auto-sync staff from sites
        $allSiteStaff = \App\Models\SiteStaff::select('name', 'salary')->distinct('name')->get();
        foreach ($allSiteStaff as $staff) {
            if (!empty($staff->name)) {
                StaffSalary::firstOrCreate(
                    ['name' => $staff->name],
                    ['salary' => $staff->salary ?? 0, 'balance' => 0]
                );
            }
        }

        $query = StaffSalary::orderBy('created_at', 'desc');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('salary', 'like', "%{$search}%");
        }

        if ($request->has('all')) {
            $salaries = $query->get();
        } else {
            $salaries = $query->paginate($request->get('per_page', 10));
        }
        
        $items = $request->has('all') ? $salaries : $salaries->items();
        foreach ($items as $salary) {
            // Calculate total working days dynamically from daily reports
            $presentCount = \App\Models\DailyReportStaff::where('name', $salary->name)
                ->where('status', 'P')
                ->count();
                
            // Update the property for the API response
            if ($presentCount > 0 || $salary->total_working_day == 0) {
                $salary->total_working_day = $presentCount;
            }
        }
        
        return response()->json($salaries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'month' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'salary' => 'nullable|numeric',
            'prv_month_due_adv' => 'nullable|numeric',
            'total_working_day' => 'nullable|numeric',
            'current_month_salary' => 'nullable|numeric',
            'debit_amount' => 'nullable|numeric',
            'remark' => 'nullable|string',
        ]);

        $prv_month_due_adv = $validated['prv_month_due_adv'] ?? 0;
        $current_month_salary = $validated['current_month_salary'] ?? 0;
        $debit_amount = $validated['debit_amount'] ?? 0;

        $balance = $prv_month_due_adv + $current_month_salary - $debit_amount;

        $staffSalary = StaffSalary::create([
            'month' => $validated['month'] ?? null,
            'name' => $validated['name'],
            'salary' => $validated['salary'] ?? 0,
            'prv_month_due_adv' => $prv_month_due_adv,
            'total_working_day' => $validated['total_working_day'] ?? 0,
            'current_month_salary' => $current_month_salary,
            'debit_amount' => $debit_amount,
            'balance' => $balance,
            'remark' => $validated['remark'] ?? null,
        ]);

        return response()->json($staffSalary, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(StaffSalary $staffSalary)
    {
        return response()->json($staffSalary);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StaffSalary $staffSalary)
    {
        $validated = $request->validate([
            'month' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'salary' => 'nullable|numeric',
            'prv_month_due_adv' => 'nullable|numeric',
            'total_working_day' => 'nullable|numeric',
            'current_month_salary' => 'nullable|numeric',
            'debit_amount' => 'nullable|numeric',
            'remark' => 'nullable|string',
        ]);

        $prv_month_due_adv = $validated['prv_month_due_adv'] ?? 0;
        $current_month_salary = $validated['current_month_salary'] ?? 0;
        $debit_amount = $validated['debit_amount'] ?? 0;

        $balance = $prv_month_due_adv + $current_month_salary - $debit_amount;

        $staffSalary->update([
            'month' => $validated['month'] ?? $staffSalary->month,
            'name' => $validated['name'],
            'salary' => $validated['salary'] ?? 0,
            'prv_month_due_adv' => $prv_month_due_adv,
            'total_working_day' => $validated['total_working_day'] ?? 0,
            'current_month_salary' => $current_month_salary,
            'debit_amount' => $debit_amount,
            'balance' => $balance,
            'remark' => $validated['remark'] ?? null,
        ]);

        return response()->json($staffSalary);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StaffSalary $staffSalary)
    {
        $staffSalary->delete();
        return response()->json(['message' => 'Record deleted successfully']);
    }

    public function getAttendance(Request $request)
    {
        $name = $request->query('name');
        
        $attendance = \App\Models\DailyReportStaff::where('name', $name)
            ->where('status', 'P')
            ->join('daily_reports', 'daily_report_staff.daily_report_id', '=', 'daily_reports.id')
            ->select('daily_reports.date')
            ->pluck('date');
            
        return response()->json($attendance);
    }
}
