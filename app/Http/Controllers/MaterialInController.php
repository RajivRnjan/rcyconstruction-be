<?php

namespace App\Http\Controllers;

use App\Models\MaterialIn;
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
     * Return material-in entries grouped by site.
     */
    public function stockBySite(Request $request)
    {
        $query = MaterialIn::with(['supplier', 'material', 'site', 'transferredFromSite'])
            ->orderBy('date', 'desc');

        if ($request->has('site_id') && !empty($request->site_id)) {
            $query->where('site_id', $request->site_id);
        }

        $entries = $query->get();

        // Group by site
        $grouped = $entries->groupBy('site_id')->map(function ($items, $siteId) {
            $site = $items->first()->site;
            return [
                'site_id'     => $siteId,
                'site_name'   => $site ? $site->name : 'Unknown',
                'total_amount'=> $items->sum('amount'),
                'entries'     => $items->values(),
            ];
        })->values();

        return response()->json($grouped);
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
