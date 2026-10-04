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

        if ($request->has('date') && !empty($request->date)) {
            $query->where('daily_reports.date', $request->date);
        }
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('daily_reports.date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('daily_reports.date', '<=', $request->end_date);
        }
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sites.name', 'like', "%{$search}%")
                  ->orWhere('daily_reports.date', 'like', "%{$search}%")
                  ->orWhere('daily_reports.site_incharge', 'like', "%{$search}%");
            });
        }
        
        $query->select('daily_reports.*', 'sites.name as site_name');
        $query->orderBy('daily_reports.date', 'desc');
        
        if ($request->has('all')) {
            return response()->json($query->get());
        } else {
            $perPage = $request->get('per_page', 10);
            return response()->json($query->paginate($perPage));
        }
    }

    
    public function getExpenseSuggestions()
    {
        $suggestions = \App\Models\DailyReportExpense::where('type', 'SITE EXPENSE')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->distinct()
            ->pluck('name');
            
        return response()->json($suggestions);
    }

    public function show($id)
    {
        $report = DailyReport::with(['staff', 'expenses', 'subcontractors'])->findOrFail($id);

        $materialIn = \App\Models\MaterialIn::where('date', $report->date)
            ->where('site_id', $report->site_id)
            ->with(['material', 'supplier'])->get();

        // Split material_outs by type
        $allOuts = \App\Models\MaterialOut::where('date', $report->date)
            ->where('site_id', $report->site_id)
            ->with(['material', 'expensesHead', 'toSite'])->get();

        $materialUsed    = $allOuts->where('type', 'used')->values();
        $materialTransfer = $allOuts->where('type', 'transfer')->values();

        return response()->json([
            'report'            => $report,
            'material_in'       => $materialIn,
            'material_used'     => $materialUsed,
            'material_transfer' => $materialTransfer,
            // keep backward compat
            'material_out'      => $allOuts,
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
            'report_id'           => 'nullable|integer|exists:daily_reports,id',
            'site_id'             => 'required|exists:sites,id',
            'date'                => 'required|date',
            'site_incharge'       => 'nullable|string',
            'outstanding_balance' => 'nullable|numeric',
            'staff_attendance'    => 'array',
            'expenses'            => 'array',
            'subcontractors'      => 'array',
            'material_in'         => 'array',
            'material_out'        => 'array',
            'material_used'       => 'array',
            'material_transfer'   => 'array',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'site_id'             => $validated['site_id'],
                'date'                => $validated['date'],
                'site_incharge'       => $validated['site_incharge'] ?? null,
                'outstanding_balance' => !empty($validated['outstanding_balance']) ? $validated['outstanding_balance'] : 0,
            ];

            if (!empty($validated['report_id'])) {
                // Editing an existing report — update by ID to avoid duplicate creation
                $report = DailyReport::findOrFail($validated['report_id']);
                $report->update($updateData);
            } else {
                // New report — use updateOrCreate on site+date to avoid duplicates
                $report = DailyReport::updateOrCreate(
                    ['site_id' => $validated['site_id'], 'date' => $validated['date']],
                    $updateData
                );
            }

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
                            'name' => $exp['name'] ?? null,
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
                            'amount' => !empty($sub['amount']) ? (float)$sub['amount'] : 0,
                            'work_details' => $sub['work_details'] ?? null,
                        ]);
                    }
                }
            }

            // Materials In
            if (!empty($validated['material_in'])) {
                MaterialIn::where('date', $validated['date'])->where('site_id', $validated['site_id'])->delete();
                foreach ($validated['material_in'] as $mat) {
                    if (!empty($mat['supplier'])) {
                        $material = \App\Models\Material::firstOrCreate(['name' => $mat['material']]);
                        MaterialIn::create([
                            'date' => $validated['date'],
                            'site_id' => $validated['site_id'],
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

            // Material Used
            MaterialOut::where('date', $validated['date'])->where('site_id', $validated['site_id'])->delete();
            if (!empty($validated['material_used'])) {
                foreach ($validated['material_used'] as $mat) {
                    if (!empty($mat['material'])) {
                        $material = \App\Models\Material::firstOrCreate(['name' => $mat['material']]);
                        MaterialOut::create([
                            'type'        => 'used',
                            'date'        => $validated['date'],
                            'site_id'     => $validated['site_id'],
                            'material_id' => $material->id,
                            'unit'        => $mat['unit'] ?? null,
                            'qnty'        => !empty($mat['qnty']) ? (float)$mat['qnty'] : 0,
                            'remark'      => $mat['remark'] ?? null,
                        ]);
                    }
                }
            }

            // Material Transfer
            if (!empty($validated['material_transfer'])) {
                // Also remove old transfer-created material_in entries for this date+site to avoid duplicates
                MaterialIn::where('date', $validated['date'])
                    ->where('site_id', '!=', $validated['site_id'])
                    ->where('transferred_from_site_id', $validated['site_id'])
                    ->delete();

                foreach ($validated['material_transfer'] as $mat) {
                    if (!empty($mat['material']) && !empty($mat['to_site'])) {
                        $material = \App\Models\Material::firstOrCreate(['name' => $mat['material']]);
                        $qnty     = !empty($mat['qnty']) ? (float)$mat['qnty'] : 0;

                        // Record the transfer in material_outs
                        MaterialOut::create([
                            'type'       => 'transfer',
                            'date'       => $validated['date'],
                            'site_id'    => $validated['site_id'],
                            'to_site_id' => (int)$mat['to_site'],
                            'material_id'=> $material->id,
                            'unit'       => $mat['unit'] ?? null,
                            'qnty'       => $qnty,
                            'remark'     => $mat['remark'] ?? null,
                        ]);

                        // Auto-create material_in for destination site
                        MaterialIn::create([
                            'date'                    => $validated['date'],
                            'site_id'                 => (int)$mat['to_site'],
                            'transferred_from_site_id'=> $validated['site_id'],
                            'material_id'             => $material->id,
                            'unit'                    => $mat['unit'] ?? null,
                            'qnty'                    => $qnty,
                            'rate'                    => 0,
                            'amount'                  => 0,
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

    public function destroy($id)
    {
        try {
            $report = DailyReport::findOrFail($id);
            
            // Delete associated Material In (materials added to this site)
            \App\Models\MaterialIn::where('date', $report->date)
                ->where('site_id', $report->site_id)
                ->delete();

            // Delete associated Material In on OTHER sites that were auto-created from this site's transfer today
            \App\Models\MaterialIn::where('date', $report->date)
                ->where('transferred_from_site_id', $report->site_id)
                ->delete();

            // Delete associated Material Out (including used and transfer)
            \App\Models\MaterialOut::where('date', $report->date)
                ->where('site_id', $report->site_id)
                ->delete();

            // Daily report relations (staff, expenses, subcontractors) will be cascade deleted
            $report->delete();
            
            return response()->json(['message' => 'Report deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete report', 'error' => $e->getMessage()], 500);
        }
    }
}
