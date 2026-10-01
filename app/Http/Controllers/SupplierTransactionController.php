<?php

namespace App\Http\Controllers;

use App\Models\SupplierTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierTransactionController extends Controller
{
    public function index($supplierId)
    {
        $transactions = SupplierTransaction::where('supplier_id', $supplierId)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($transactions);
    }

    public function store(Request $request, $supplierId)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'invoice_no' => 'nullable|string|max:255',
            'payment' => 'nullable|numeric',
            'invoice_amount' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();

            $supplier = Supplier::findOrFail($supplierId);
            
            $payment = $validated['payment'] ?? 0;
            $invoice_amount = $validated['invoice_amount'] ?? 0;
            
            $outstanding_balance = $supplier->outstanding_amount;
            $balance = $outstanding_balance + $invoice_amount - $payment;

            $transaction = SupplierTransaction::create([
                'supplier_id' => $supplierId,
                'date' => $validated['date'] ?? null,
                'invoice_no' => $validated['invoice_no'] ?? null,
                'outstanding_balance' => $outstanding_balance,
                'payment' => $payment,
                'invoice_amount' => $invoice_amount,
                'balance' => $balance
            ]);

            // Update supplier outstanding amount
            $supplier->outstanding_amount = $balance;
            $supplier->save();

            DB::commit();

            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating transaction', 'error' => $e->getMessage()], 500);
        }
    }
}
