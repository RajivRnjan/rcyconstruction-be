<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\MaterialIn;
use App\Models\MaterialOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyReportController extends Controller
{

    public function index(Request $request)
    {
        $query = DailyReport::with(['staff', 'expenses', 'subcontractors']);
        
        // We also want to load the site and its project to filter
        // Actually, Site model needs a project relationship if not already
        $query->join('sites', 'daily_reports.site_id', '=', 'sites.id');
        
        if ($request->has('site_id') && !empty($request->site_id)) {
            $query->where('daily_reports.site_id', $request->site_id);
        }
        if ($request->has('site_id') && !empty($request->site_id)) {
            $query->where('sites.site_id', $request->site_id);
        }
        
        $query->select('daily_reports.*', 'sites.name as site_name', 'sites.site_id');
        $query->orderBy('daily_reports.date', 'desc');
        
        return response()->json($query->get());
    }

    public function show($id)
    {
        $report = DailyReport::with(['staff', 'expenses', 'subcontractors'])->findOrFail($id);
        
        // Also fetch material in/out for that date and site
        $materialIn = \App\Models\MaterialIn::where('date', $report->date)
            ->with('material')->get();
            
        $materialOut = \App\Models\MaterialOut::where('date', $report->date)
            ->with(['material', 'expensesHead'])->get();
            
        return response()->json([
            'report' => $report,
            'material_in' => $materialIn,
            'material_out' => $materialOut
        ]);
    }

    public function indexBySite($site_id)
    {
        $reports = DailyReport::where('site_id', $site_id)->orderBy('date', 'desc')->get();
        return response()->json($reports);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'date' => 'required|date',
            'site_incharge' => 'nullable|string',
            'outstanding_balance' => 'nullable|numeric',
            'staff_attendance' => 'array',
            'expenses' => 'array',
            'subcontractors' => 'array',
            'material_in' => 'array',
            'material_out' => 'array',
        ]);

        try {
            DB::beginTransaction();

            $report = DailyReport::updateOrCreate(
                ['site_id' => $validated['site_id'], 'date' => $validated['date']],
                [
                    'site_incharge' => $validated['site_incharge'] ?? null,
                    'outstanding_balance' => !empty($validated['outstanding_balance']) ? $validated['outstanding_balance'] : 0,
                ]
            );

            // Staff
            if (!empty($validated['staff_attendance'])) {
                $report->staff()->delete();
                foreach ($validated['staff_attendance'] as $staff) {
                    $report->staff()->create([
                        'staff_id' => $staff['staff_id'],
                        'name' => $staff['name'],
                        'status' => $staff['status'],
                    ]);
                }
            }

            // Expenses
            if (!empty($validated['expenses'])) {
                $report->expenses()->delete();
                foreach ($validated['expenses'] as $exp) {
                    if (!empty($exp['type'])) {
                        $report->expenses()->create([
                            'type' => $exp['type'],
                            'amount' => !empty($exp['amount']) ? (float)$exp['amount'] : 0,
                        ]);
                    }
                }
            }

            // Subcontractors
            if (!empty($validated['subcontractors'])) {
                $report->subcontractors()->delete();
                foreach ($validated['subcontractors'] as $sub) {
                    if (!empty($sub['name'])) {
                        $report->subcontractors()->create([
                            'name' => $sub['name'],
                            'no_of_labour' => !empty($sub['no_of_labour']) ? (int)$sub['no_of_labour'] : 0,
                            'work_details' => $sub['work_details'] ?? null,
                        ]);
                    }
                }
            }

            // Materials In
            if (!empty($validated['material_in'])) {
                MaterialIn::where('date', $validated['date'])->delete(); // simplistic approach
                foreach ($validated['material_in'] as $mat) {
                    if (!empty($mat['supplier'])) {
                        $material = \App\Models\Material::firstOrCreate(['name' => $mat['material']]);
                        MaterialIn::create([
                            'date' => $validated['date'],
                            'supplier_id' => $mat['supplier'],
                            'material_id' => $material->id,
                            'unit' => $mat['unit'] ?? null,
                            'qnty' => !empty($mat['qnty']) ? (float)$mat['qnty'] : 0,
                            'rate' => !empty($mat['rate']) ? (float)$mat['rate'] : 0,
                            'amount' => !empty($mat['amount']) ? (float)$mat['amount'] : 0,
                        ]);
                    }
                }
            }

            // Materials Out
            if (!empty($validated['material_out'])) {
                MaterialOut::where('date', $validated['date'])->delete();
                foreach ($validated['material_out'] as $mat) {
                    if (!empty($mat['supplier'])) {
                        $material = \App\Models\Material::firstOrCreate(['name' => $mat['material']]);
                        
                        $expHeadId = null;
                        if (!empty($mat['expense_head'])) {
                            $eh = \App\Models\ExpensesHead::firstOrCreate(['name' => $mat['expense_head']]);
                            $expHeadId = $eh->id;
                        }
                        
                        // Get site_id from site
                        $site = \App\Models\Site::find($validated['site_id']);
                        
                        MaterialOut::create([
                            'date' => $validated['date'],
                            'site_id' => $site ? $site->site_id : null,
                            'supplier_id' => $mat['supplier'],
                            'material_id' => $material->id,
                            'expenses_head_id' => $expHeadId,
                            'qnty' => !empty($mat['qnty']) ? (float)$mat['qnty'] : 0,
                            'rate' => !empty($mat['rate']) ? (float)$mat['rate'] : 0,
                            'amount' => !empty($mat['amount']) ? (float)$mat['amount'] : 0,
                            'paid' => !empty($mat['paid']) ? (float)$mat['paid'] : 0,
                            'balance' => !empty($mat['balance']) ? (float)$mat['balance'] : 0,
                            'remark' => $mat['remark'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Daily Report saved successfully', 'report' => $report], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to save Daily Report', 'error' => $e->getMessage()], 500);
        }
    }
}
