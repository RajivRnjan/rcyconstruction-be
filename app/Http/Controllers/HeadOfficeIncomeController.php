<?php

namespace App\Http\Controllers;

use App\Models\HeadOfficeIncome;
use Illuminate\Http\Request;

class HeadOfficeIncomeController extends Controller
{
    public function index()
    {
        return response()->json(HeadOfficeIncome::with(['project', 'account'])->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'site_id' => 'required|exists:sites,id',
            'client_name' => 'nullable|string|max:255',
            'mode_of_payment' => 'nullable|string|max:255',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        $income = HeadOfficeIncome::create($validated);

        return response()->json($income->load(['project', 'account']), 201);
    }

    public function show(HeadOfficeIncome $headOfficeIncome)
    {
        return response()->json($headOfficeIncome->load(['project', 'account']));
    }

    public function update(Request $request, HeadOfficeIncome $headOfficeIncome)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'site_id' => 'required|exists:sites,id',
            'client_name' => 'nullable|string|max:255',
            'mode_of_payment' => 'nullable|string|max:255',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        $headOfficeIncome->update($validated);

        return response()->json($headOfficeIncome->load(['project', 'account']));
    }

    public function destroy(HeadOfficeIncome $headOfficeIncome)
    {
        $headOfficeIncome->delete();
        return response()->json(null, 204);
    }
}
