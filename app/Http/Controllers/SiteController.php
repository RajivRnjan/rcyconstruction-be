<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::with(['siteIncharge', 'boqItems', 'siteStaff', 'siteSubcontractors'])->get();
        return response()->json($sites);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'site_incharge_id' => 'nullable|exists:site_incharges,id',
            'staff' => 'nullable|array',
            'staff.*.name' => 'required|string',
            'staff.*.salary' => 'nullable|numeric',
            'subcontractors' => 'nullable|array',
            'subcontractors.*.name' => 'required|string',
            'boq_name' => 'nullable|string',
            'site_exp' => 'nullable|numeric',
            'np' => 'nullable|numeric',
            'agreement_value' => 'nullable|numeric',
            'upto_date_bill_value' => 'nullable|numeric',
            'balance_work_value' => 'nullable|numeric',
            'boq_items' => 'array',
            'boq_items.*.item_name' => 'required|string',
            'boq_items.*.est_qnt' => 'required|numeric',
            'boq_items.*.unit' => 'nullable|string',
            'boq_items.*.rate' => 'required|numeric',
            'boq_items.*.work_done_qty' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $agreement_value = $validated['agreement_value'] ?? 0;
            $upto_date_bill_value = $validated['upto_date_bill_value'] ?? 0;
            $balance_work_value = $validated['balance_work_value'] ?? 0;

            if (isset($validated['boq_items']) && count($validated['boq_items']) > 0) {
                $agreement_value = 0;
                $upto_date_bill_value = 0;
                foreach ($validated['boq_items'] as $item) {
                    $agreement_value += ($item['est_qnt'] * $item['rate']);
                    $upto_date_bill_value += ($item['work_done_qty'] * $item['rate']);
                }
                $balance_work_value = $agreement_value - $upto_date_bill_value;
            } else if (!isset($validated['balance_work_value'])) {
                $balance_work_value = $agreement_value - $upto_date_bill_value;
            }

                        $site = Site::create([
                'name' => $validated['name'],
                'site_incharge_id' => $validated['site_incharge_id'] ?? null,
                'boq_name' => $validated['boq_name'] ?? null,
                'site_exp' => $validated['site_exp'] ?? 0,
                'np' => $validated['np'] ?? 0,
                'agreement_value' => $agreement_value,
                'upto_date_bill_value' => $upto_date_bill_value,
                'balance_work_value' => $balance_work_value,
            ]);

            if (isset($validated['boq_items'])) {
                foreach ($validated['boq_items'] as $item) {
                    $site->boqItems()->create($item);
                }
            }

            if (isset($validated['subcontractors'])) {
                foreach ($validated['subcontractors'] as $sub) {
                    if (!empty($sub['name'])) {
                        $site->siteSubcontractors()->create([
                            'name' => $sub['name']
                        ]);
                        // Also push to global subcontractors
                        \App\Models\Subcontractor::firstOrCreate([
                            'name' => $sub['name']
                        ], [
                            'site_id' => $site->id
                        ]);
                    }
                }
            }
            if (isset($validated['staff'])) {
                foreach ($validated['staff'] as $staff) {
                    if (!empty($staff['name'])) {
                        $site->siteStaff()->create([
                            'name' => $staff['name'],
                            'salary' => !empty($staff['salary']) ? $staff['salary'] : 0
                        ]);
                        // Also push to global staff salary
                        \App\Models\StaffSalary::firstOrCreate([
                            'name' => $staff['name']
                        ], [
                            'salary' => !empty($staff['salary']) ? $staff['salary'] : 0,
                            'balance' => 0
                        ]);
                    }
                }
            }


            DB::commit();

            return response()->json($site->load(['siteIncharge', 'boqItems']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Error creating Site', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $site = Site::with(['siteIncharge', 'boqItems', 'siteStaff', 'siteSubcontractors'])->findOrFail($id);
        return response()->json($site);
    }

    public function update(Request $request, $id)
    {
        $site = Site::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'site_incharge_id' => 'nullable|exists:site_incharges,id',
            'staff' => 'nullable|array',
            'staff.*.name' => 'required|string',
            'staff.*.salary' => 'nullable|numeric',
            'subcontractors' => 'nullable|array',
            'subcontractors.*.name' => 'required|string',
            'boq_name' => 'nullable|string',
            'site_exp' => 'nullable|numeric',
            'np' => 'nullable|numeric',
            'agreement_value' => 'nullable|numeric',
            'upto_date_bill_value' => 'nullable|numeric',
            'balance_work_value' => 'nullable|numeric',
            'boq_items' => 'array',
            'boq_items.*.id' => 'nullable|integer',
            'boq_items.*.item_name' => 'required|string',
            'boq_items.*.est_qnt' => 'required|numeric',
            'boq_items.*.unit' => 'nullable|string',
            'boq_items.*.rate' => 'required|numeric',
            'boq_items.*.work_done_qty' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $agreement_value = $validated['agreement_value'] ?? 0;
            $upto_date_bill_value = $validated['upto_date_bill_value'] ?? 0;
            $balance_work_value = $validated['balance_work_value'] ?? 0;

            if (isset($validated['boq_items']) && count($validated['boq_items']) > 0) {
                $agreement_value = 0;
                $upto_date_bill_value = 0;
                foreach ($validated['boq_items'] as $item) {
                    $agreement_value += ($item['est_qnt'] * $item['rate']);
                    $upto_date_bill_value += ($item['work_done_qty'] * $item['rate']);
                }
                $balance_work_value = $agreement_value - $upto_date_bill_value;
            } else if (!isset($validated['balance_work_value'])) {
                $balance_work_value = $agreement_value - $upto_date_bill_value;
            }

                        $site->update([
                'name' => $validated['name'],
                'site_incharge_id' => $validated['site_incharge_id'] ?? null,
                'boq_name' => $validated['boq_name'] ?? null,
                'site_exp' => $validated['site_exp'] ?? 0,
                'np' => $validated['np'] ?? 0,
                'agreement_value' => $agreement_value,
                'upto_date_bill_value' => $upto_date_bill_value,
                'balance_work_value' => $balance_work_value,
            ]);

            if (isset($validated['boq_items'])) {
                $site->boqItems()->delete();
                foreach ($validated['boq_items'] as $item) {
                    $site->boqItems()->create([
                        'item_name' => $item['item_name'],
                        'est_qnt' => $item['est_qnt'],
                        'unit' => $item['unit'] ?? null,
                        'rate' => $item['rate'],
                        'work_done_qty' => $item['work_done_qty'],
                    ]);
                }
            } else {
                $site->boqItems()->delete();
            }

            if (isset($validated['subcontractors'])) {
                foreach ($validated['subcontractors'] as $sub) {
                    $site->siteSubcontractors()->create([
                        'name' => $sub['name']
                    ]);
                }
            }

            if (isset($validated['subcontractors'])) {
                $site->siteSubcontractors()->delete();
                foreach ($validated['subcontractors'] as $sub) {
                    $site->siteSubcontractors()->create([
                        'name' => $sub['name']
                    ]);
                }
            } else {
                $site->siteSubcontractors()->delete();
            }
            if (isset($validated['staff'])) {
                $site->siteStaff()->delete();
                foreach ($validated['staff'] as $staff) {
                    $site->siteStaff()->create([
                        'name' => $staff['name'],
                        'salary' => !empty($staff['salary']) ? $staff['salary'] : 0
                    ]);
                }
            } else {
                $site->siteStaff()->delete();
            }


            DB::commit();

            return response()->json($site->load(['siteIncharge', 'boqItems']), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage()); return response()->json(['message' => 'Error updating Site', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $site = Site::findOrFail($id);
            $site->boqItems()->delete();
            $site->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting Site', 'error' => $e->getMessage()], 500);
        }
    }

    public function addStaff(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'salary' => 'nullable|numeric'
        ]);

        $site = Site::findOrFail($id);
        $staff = $site->siteStaff()->create([
            'name' => $validated['name'],
            'salary' => !empty($validated['salary']) ? $validated['salary'] : 0
        ]);

        // Push to global staff salary
        \App\Models\StaffSalary::firstOrCreate([
            'name' => $validated['name']
        ], [
            'salary' => !empty($validated['salary']) ? $validated['salary'] : 0,
            'balance' => 0
        ]);

        return response()->json($staff, 201);
    }

    public function removeStaff($id, $staff_id)
    {
        try {
            $site = Site::findOrFail($id);
            $site->siteStaff()->where('id', $staff_id)->delete();
            return response()->json(['message' => 'Staff removed from site successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error removing staff', 'error' => $e->getMessage()], 500);
        }
    }
}
