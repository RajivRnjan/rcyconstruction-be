<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterSheet;
use App\Models\BoqItem;
use Illuminate\Support\Facades\DB;

class MasterSheetController extends Controller
{
    public function index()
    {
        $sheets = MasterSheet::with('boqItems')->orderBy('created_at', 'desc')->get();
        return response()->json($sheets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string',
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
                // If not explicitly set and no BOQ items, auto calculate balance if possible
                $balance_work_value = $agreement_value - $upto_date_bill_value;
            }

            $sheet = MasterSheet::create([
                'project_name' => $validated['project_name'],
                'boq_name' => $validated['boq_name'] ?? null,
                'site_exp' => $validated['site_exp'] ?? 0,
                'np' => $validated['np'] ?? 0,
                'agreement_value' => $agreement_value,
                'upto_date_bill_value' => $upto_date_bill_value,
                'balance_work_value' => $balance_work_value,
            ]);

            if (isset($validated['boq_items'])) {
                foreach ($validated['boq_items'] as $item) {
                    $sheet->boqItems()->create($item);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Master Sheet created successfully',
                'data' => $sheet->load('boqItems')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating Master Sheet', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $sheet = MasterSheet::with('boqItems')->findOrFail($id);
        return response()->json($sheet);
    }

    public function update(Request $request, $id)
    {
        $sheet = MasterSheet::findOrFail($id);

        $validated = $request->validate([
            'project_name' => 'required|string',
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

            $sheet->update([
                'project_name' => $validated['project_name'],
                'boq_name' => $validated['boq_name'] ?? null,
                'site_exp' => $validated['site_exp'] ?? 0,
                'np' => $validated['np'] ?? 0,
                'agreement_value' => $agreement_value,
                'upto_date_bill_value' => $upto_date_bill_value,
                'balance_work_value' => $balance_work_value,
            ]);

            // Sync BOQ Items
            if (isset($validated['boq_items'])) {
                // Delete existing ones not in the new list (if any logic needed)
                // For simplicity, we can just delete all and recreate, or update existing.
                // Let's delete all and recreate to ensure it's fully synced
                $sheet->boqItems()->delete();

                foreach ($validated['boq_items'] as $item) {
                    $sheet->boqItems()->create([
                        'item_name' => $item['item_name'],
                        'est_qnt' => $item['est_qnt'],
                        'unit' => $item['unit'] ?? null,
                        'rate' => $item['rate'],
                        'work_done_qty' => $item['work_done_qty'],
                    ]);
                }
            } else {
                $sheet->boqItems()->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Master Sheet updated successfully',
                'data' => $sheet->load('boqItems')
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating Master Sheet', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $sheet = MasterSheet::findOrFail($id);
            $sheet->boqItems()->delete(); // cascade delete BOQ items
            $sheet->delete();
            return response()->json(['message' => 'Master Sheet deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting Master Sheet', 'error' => $e->getMessage()], 500);
        }
    }
}
