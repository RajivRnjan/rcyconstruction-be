<?php

namespace App\Http\Controllers;

use App\Models\MaterialIn;
use Illuminate\Http\Request;

class MaterialInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(MaterialIn::with(['supplier', 'material'])->orderBy('created_at', 'desc')->get());
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
