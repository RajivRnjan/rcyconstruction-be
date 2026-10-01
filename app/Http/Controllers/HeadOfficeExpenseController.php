<?php

namespace App\Http\Controllers;

use App\Models\HeadOfficeExpense;
use Illuminate\Http\Request;

class HeadOfficeExpenseController extends Controller
{
    public function index(Request $request)
    {
        // Load the new polymorphic-like relationships if they exist on the model
        $query = HeadOfficeExpense::with(['project', 'supplier', 'account'])->orderBy('created_at', 'desc');

        if ($request->has('site_id') && !empty($request->site_id)) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('expenses_head', 'like', "%{$search}%")
                  ->orWhere('person_name', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('account', function($q2) use ($search) {
                      $q2->where('account_details', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('all')) {
            $expenses = $query->get();
        } else {
            $perPage = $request->get('per_page', 10);
            $expenses = $query->paginate($perPage);
        }

        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'site_id' => 'required|exists:sites,id',
            'expenses_head' => 'required|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'subcontractor_id' => 'nullable|exists:subcontractors,id',
            'staff_id' => 'nullable|exists:staff_salaries,id',
            'person_name' => 'nullable|string|max:255',
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
            'supplier_id' => 'nullable|exists:suppliers,id',
            'subcontractor_id' => 'nullable|exists:subcontractors,id',
            'staff_id' => 'nullable|exists:staff_salaries,id',
            'person_name' => 'nullable|string|max:255',
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
