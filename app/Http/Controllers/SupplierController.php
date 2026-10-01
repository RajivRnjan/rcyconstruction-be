<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::orderBy('created_at', 'desc');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhere('gst_number', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->has('all')) {
            $suppliers = $query->get();
        } else {
            $perPage = $request->get('per_page', 10);
            $suppliers = $query->paginate($perPage);
        }

        $items = $request->has('all') ? $suppliers : $suppliers->items();
        foreach($items as $supplier) {
            $supplier->total_paid = \App\Models\DailyReportExpense::where('name', $supplier->name)
                ->whereIn('type', ['SUPPLIER PAYMENT', 'PARTY PAYMENT'])
                ->sum('amount');
        }

        return response()->json($suppliers);
    }

    public function payments(Request $request)
    {
        $name = $request->query('name');
        $payments = \App\Models\DailyReportExpense::where('name', $name)
            ->whereIn('type', ['SUPPLIER PAYMENT', 'PARTY PAYMENT'])
            ->join('daily_reports', 'daily_report_expenses.daily_report_id', '=', 'daily_reports.id')
            ->select('daily_reports.date', 'daily_report_expenses.amount')
            ->orderBy('daily_reports.date', 'desc')
            ->get();
            
        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:255',
        ]);

        $supplier = Supplier::create($validated);
        return response()->json($supplier, 201);
    }

    public function show(Supplier $supplier)
    {
        return response()->json($supplier);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:255',
        ]);

        $supplier->update($validated);
        return response()->json($supplier);
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted successfully']);
    }
}
