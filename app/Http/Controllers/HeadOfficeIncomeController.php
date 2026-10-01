<?php

namespace App\Http\Controllers;

use App\Models\HeadOfficeIncome;
use Illuminate\Http\Request;

class HeadOfficeIncomeController extends Controller
{
    public function index(Request $request)
    {
        $query = HeadOfficeIncome::with(['project', 'account'])->orderBy('created_at', 'desc');

        if ($request->has('site_id') && !empty($request->site_id)) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhereHas('account', function($q2) use ($search) {
                      $q2->where('account_details', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('all')) {
            $incomes = $query->get();
        } else {
            $perPage = $request->get('per_page', 10);
            $incomes = $query->paginate($perPage);
        }

        return response()->json($incomes);
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
