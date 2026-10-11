<?php

namespace App\Http\Controllers;

use App\Models\MaterialIn;
use App\Models\MaterialOut;
use App\Models\Site;
use Illuminate\Http\Request;

class MaterialInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(MaterialIn::with(['supplier', 'material', 'site'])->orderBy('created_at', 'desc')->get());
    }

    /**
     * Return material stock aggregated by material and site.
     */
    public function stockBySite(Request $request)
    {
        $siteId = $request->get('site_id');

        // 1. Fetch MaterialIn (purchases and incoming transfers)
        $inQuery = MaterialIn::with(['supplier', 'material', 'site', 'transferredFromSite'])
            ->orderBy('date', 'desc');
        if (!empty($siteId)) {
            $inQuery->where('site_id', $siteId);
        }
        $materialIns = $inQuery->get();

        // 2. Fetch MaterialOut (used and outgoing transfers)
        $outQuery = MaterialOut::with(['material', 'site', 'toSite', 'supplier'])
            ->orderBy('date', 'desc');
        if (!empty($siteId)) {
            $outQuery->where('site_id', $siteId);
        }
        $materialOuts = $outQuery->get();

        // Map Material In entries
        $mappedIns = $materialIns->map(function ($item) {
            $matName = $item->material ? $item->material->name : 'Unknown Material';
            $isTransfer = !empty($item->transferred_from_site_id);
            return [
                'id'                   => 'in-' . $item->id,
                'raw_id'               => $item->id,
                'entry_type'           => 'in',
                'movement_type'        => $isTransfer ? 'TRANSFER IN' : 'IN (PURCHASE)',
                'date'                 => $item->date,
                'material_id'          => $item->material_id,
                'material_name'        => $matName,
                'site_id'              => $item->site_id,
                'site_name'            => $item->site ? $item->site->name : 'Unknown Site',
                'unit'                 => $item->unit ?: ($item->material->unit ?? ''),
                'qnty'                 => (float)($item->qnty ?? 0),
                'in_qnty'              => (float)($item->qnty ?? 0),
                'out_qnty'             => 0,
                'rate'                 => (float)($item->rate ?? 0),
                'amount'               => (float)($item->amount ?? 0),
                'supplier_name'        => $item->supplier ? $item->supplier->name : null,
                'source_destination'   => $isTransfer 
                    ? ('Transferred from ' . ($item->transferredFromSite ? $item->transferredFromSite->name : 'Other Site'))
                    : ($item->supplier ? $item->supplier->name : '-'),
                'remark'               => null,
                'created_at'           => $item->created_at,
            ];
        });

        // Map Material Out entries
        $mappedOuts = $materialOuts->map(function ($item) {
            $matName = $item->material ? $item->material->name : 'Unknown Material';
            $isTransfer = ($item->type === 'transfer');
            return [
                'id'                   => 'out-' . $item->id,
                'raw_id'               => $item->id,
                'entry_type'           => 'out',
                'movement_type'        => $isTransfer ? 'TRANSFER OUT' : 'USED',
                'date'                 => $item->date,
                'material_id'          => $item->material_id,
                'material_name'        => $matName,
                'site_id'              => $item->site_id,
                'site_name'            => $item->site ? $item->site->name : 'Unknown Site',
                'unit'                 => $item->unit ?: ($item->material->unit ?? ''),
                'qnty'                 => (float)($item->qnty ?? 0),
                'in_qnty'              => 0,
                'out_qnty'             => (float)($item->qnty ?? 0),
                'rate'                 => (float)($item->rate ?? 0),
                'amount'               => (float)($item->amount ?? 0),
                'supplier_name'        => null,
                'source_destination'   => $isTransfer 
                    ? ('Transferred to ' . ($item->toSite ? $item->toSite->name : 'Other Site'))
                    : ($item->remark ?: 'Site Consumption'),
                'remark'               => $item->remark,
                'created_at'           => $item->created_at,
            ];
        });

        // Combine all entries sorted by date desc
        $allEntries = $mappedIns->concat($mappedOuts)->sortByDesc(function ($entry) {
            return ($entry['date'] ?? '') . ' ' . ($entry['created_at'] ?? '');
        })->values();

        // Group by Material Name
        $materialsGrouped = $allEntries->groupBy(function ($entry) {
            return trim(strtoupper($entry['material_name']));
        })->map(function ($entries, $matKey) {
            $first = $entries->first();
            $totalIn = (float)$entries->where('entry_type', 'in')->sum('in_qnty');
            $totalUsed = (float)$entries->where('movement_type', 'USED')->sum('out_qnty');
            $totalTransferOut = (float)$entries->where('movement_type', 'TRANSFER OUT')->sum('out_qnty');
            $currentStock = $totalIn - ($totalUsed + $totalTransferOut);
            $totalAmount = (float)$entries->where('entry_type', 'in')->sum('amount');
            
            // Prefer unit from material or latest entry
            $units = $entries->pluck('unit')->filter()->unique()->values();
            $unit = $units->first() ?: ($first['unit'] ?? '');

            return [
                'material_id'         => $first['material_id'],
                'material_name'       => $first['material_name'],
                'unit'                => $unit,
                'total_in'            => $totalIn,
                'total_used'          => $totalUsed,
                'total_transfer_out'  => $totalTransferOut,
                'current_stock'       => $currentStock,
                'total_amount'        => $totalAmount,
                'entries_count'       => $entries->count(),
                'entries'             => $entries->values(),
            ];
        })->values()->sortBy('material_name')->values();

        // Group by Site
        $sitesGrouped = $allEntries->groupBy('site_id')->map(function ($entries, $siteId) {
            $first = $entries->first();
            return [
                'site_id'     => $siteId,
                'site_name'   => $first['site_name'],
                'total_amount'=> (float)$entries->where('entry_type', 'in')->sum('amount'),
                'entries'     => $entries->values(),
            ];
        })->values();

        $allSites = Site::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'materials'    => $materialsGrouped,
            'by_site'      => $sitesGrouped,
            'sites'        => $allSites,
            'summary'      => [
                'total_materials'    => $materialsGrouped->count(),
                'total_entries'      => $allEntries->count(),
                'total_in'           => (float)$allEntries->where('entry_type', 'in')->sum('in_qnty'),
                'total_used'         => (float)$allEntries->where('movement_type', 'USED')->sum('out_qnty'),
                'total_transfer_out' => (float)$allEntries->where('movement_type', 'TRANSFER OUT')->sum('out_qnty'),
                'total_amount'       => (float)$allEntries->where('entry_type', 'in')->sum('amount'),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'material_id' => 'required|exists:materials,id',
            'unit' => 'nullable|string',
            'qnty' => 'nullable|numeric',
            'rate' => 'nullable|numeric',
        ]);

        $qnty = $validated['qnty'] ?? 0;
        $rate = $validated['rate'] ?? 0;
        $amount = $qnty * $rate;

        $materialIn = MaterialIn::create([
            'date' => $validated['date'] ?? null,
            'supplier_id' => $validated['supplier_id'],
            'material_id' => $validated['material_id'],
            'unit' => $validated['unit'] ?? null,
            'qnty' => $qnty,
            'rate' => $rate,
            'amount' => $amount,
        ]);

        return response()->json($materialIn->load(['supplier', 'material']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MaterialIn $materialIn)
    {
        return response()->json($materialIn->load(['supplier', 'material']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaterialIn $materialIn)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'material_id' => 'required|exists:materials,id',
            'unit' => 'nullable|string',
            'qnty' => 'nullable|numeric',
            'rate' => 'nullable|numeric',
        ]);

        $qnty = $validated['qnty'] ?? 0;
        $rate = $validated['rate'] ?? 0;
        $amount = $qnty * $rate;

        $materialIn->update([
            'date' => $validated['date'] ?? $materialIn->date,
            'supplier_id' => $validated['supplier_id'],
            'material_id' => $validated['material_id'],
            'unit' => $validated['unit'] ?? $materialIn->unit,
            'qnty' => $qnty,
            'rate' => $rate,
            'amount' => $amount,
        ]);

        return response()->json($materialIn->load(['supplier', 'material']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaterialIn $materialIn)
    {
        $materialIn->delete();
        return response()->json(['message' => 'Record deleted successfully']);
    }
}
