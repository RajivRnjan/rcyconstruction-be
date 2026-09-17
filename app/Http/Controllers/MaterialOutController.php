<?php

namespace App\Http\Controllers;

use App\Models\MaterialOut;
use Illuminate\Http\Request;

class MaterialOutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(MaterialOut::with(['project', 'expensesHead', 'supplier', 'material'])->orderBy('created_at', 'desc')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'project_id' => 'required|exists:master_sheets,id',
            'expenses_head_id' => 'required|exists:expenses_heads,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'material_id' => 'required|exists:materials,id',
            'unit' => 'nullable|string',
            'qnty' => 'nullable|numeric',
            'rate' => 'nullable|numeric',
            'paid' => 'nullable|numeric',
            'remark' => 'nullable|string',
        ]);

        $qnty = $validated['qnty'] ?? 0;
        $rate = $validated['rate'] ?? 0;
        $paid = $validated['paid'] ?? 0;
        
        $amount = $qnty * $rate;
        $balance = $amount - $paid;

        $materialOut = MaterialOut::create([
            'date' => $validated['date'] ?? null,
            'project_id' => $validated['project_id'],
            'expenses_head_id' => $validated['expenses_head_id'],
            'supplier_id' => $validated['supplier_id'],
            'material_id' => $validated['material_id'],
            'unit' => $validated['unit'] ?? null,
            'qnty' => $qnty,
            'rate' => $rate,
            'amount' => $amount,
            'paid' => $paid,
            'balance' => $balance,
            'remark' => $validated['remark'] ?? null,
        ]);

        return response()->json($materialOut->load(['project', 'expensesHead', 'supplier', 'material']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MaterialOut $materialOut)
    {
        return response()->json($materialOut->load(['project', 'expensesHead', 'supplier', 'material']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaterialOut $materialOut)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'project_id' => 'required|exists:master_sheets,id',
            'expenses_head_id' => 'required|exists:expenses_heads,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'material_id' => 'required|exists:materials,id',
            'unit' => 'nullable|string',
            'qnty' => 'nullable|numeric',
            'rate' => 'nullable|numeric',
            'paid' => 'nullable|numeric',
            'remark' => 'nullable|string',
        ]);

        $qnty = $validated['qnty'] ?? 0;
        $rate = $validated['rate'] ?? 0;
        $paid = $validated['paid'] ?? 0;
        
        $amount = $qnty * $rate;
        $balance = $amount - $paid;

        $materialOut->update([
            'date' => $validated['date'] ?? $materialOut->date,
            'project_id' => $validated['project_id'],
            'expenses_head_id' => $validated['expenses_head_id'],
            'supplier_id' => $validated['supplier_id'],
            'material_id' => $validated['material_id'],
            'unit' => $validated['unit'] ?? $materialOut->unit,
            'qnty' => $qnty,
            'rate' => $rate,
            'amount' => $amount,
            'paid' => $paid,
            'balance' => $balance,
            'remark' => $validated['remark'] ?? null,
        ]);

        return response()->json($materialOut->load(['project', 'expensesHead', 'supplier', 'material']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaterialOut $materialOut)
    {
        $materialOut->delete();
        return response()->json(['message' => 'Record deleted successfully']);
    }
}
