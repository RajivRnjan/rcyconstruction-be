<?php

namespace App\Http\Controllers;

use App\Models\HeadOfficeExpense;
use Illuminate\Http\Request;

class HeadOfficeExpenseController extends Controller
{
    public function index()
    {
        return response()->json(HeadOfficeExpense::with(['project', 'supplier', 'account'])->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'site_id' => 'required|exists:sites,id',
            'expenses_head' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'mode_of_payment' => 'nullable|string|max:255',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric',
            'remark' => 'nullable|string',
        ]);

        $expense = HeadOfficeExpense::create($validated);

        return response()->json($expense->load(['project', 'supplier', 'account']), 201);
    }

    public function show(HeadOfficeExpense $headOfficeExpense)
    {
        return response()->json($headOfficeExpense->load(['project', 'supplier', 'account']));
    }

    public function update(Request $request, HeadOfficeExpense $headOfficeExpense)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'site_id' => 'required|exists:sites,id',
            'expenses_head' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'mode_of_payment' => 'nullable|string|max:255',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric',
            'remark' => 'nullable|string',
        ]);

        $headOfficeExpense->update($validated);

        return response()->json($headOfficeExpense->load(['project', 'supplier', 'account']));
    }

    public function destroy(HeadOfficeExpense $headOfficeExpense)
    {
        $headOfficeExpense->delete();
        return response()->json(null, 204);
    }
}
