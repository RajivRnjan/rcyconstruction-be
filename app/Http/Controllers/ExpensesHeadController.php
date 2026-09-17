<?php

namespace App\Http\Controllers;

use App\Models\ExpensesHead;
use Illuminate\Http\Request;

class ExpensesHeadController extends Controller
{
    public function index()
    {
        return response()->json(ExpensesHead::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $expensesHead = ExpensesHead::create($validated);
        return response()->json($expensesHead, 201);
    }

    public function show(ExpensesHead $expensesHead)
    {
        return response()->json($expensesHead);
    }

    public function update(Request $request, ExpensesHead $expensesHead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $expensesHead->update($validated);
        return response()->json($expensesHead);
    }

    public function destroy(ExpensesHead $expensesHead)
    {
        $expensesHead->delete();
        return response()->json(['message' => 'Expenses Head deleted successfully']);
    }
}
