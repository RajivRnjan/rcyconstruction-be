<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    public function index()
    {
        return response()->json(Account::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_details' => 'required|string',
            'opening_balance' => 'nullable|numeric',
            'receipt_amount' => 'nullable|numeric',
            'payment_amount' => 'nullable|numeric',
            'details' => 'nullable|string',
        ]);

        $opening = $validated['opening_balance'] ?? 0;
        $receipt = $validated['receipt_amount'] ?? 0;
        $payment = $validated['payment_amount'] ?? 0;
        $balance = $opening + $receipt - $payment;

        $account = Account::create([
            'account_details' => $validated['account_details'],
            'opening_balance' => $opening,
            'receipt_amount' => $receipt,
            'payment_amount' => $payment,
            'balance' => $balance,
            'details' => $validated['details'] ?? null,
        ]);

        return response()->json(['message' => 'Account created successfully', 'data' => $account], 201);
    }

    public function show($id)
    {
        $account = Account::findOrFail($id);
        return response()->json($account);
    }

    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        $validated = $request->validate([
            'account_details' => 'required|string',
            'opening_balance' => 'nullable|numeric',
            'receipt_amount' => 'nullable|numeric',
            'payment_amount' => 'nullable|numeric',
            'details' => 'nullable|string',
        ]);

        $opening = $validated['opening_balance'] ?? 0;
        $receipt = $validated['receipt_amount'] ?? 0;
        $payment = $validated['payment_amount'] ?? 0;
        $balance = $opening + $receipt - $payment;

        $account->update([
            'account_details' => $validated['account_details'],
            'opening_balance' => $opening,
            'receipt_amount' => $receipt,
            'payment_amount' => $payment,
            'balance' => $balance,
            'details' => $validated['details'] ?? null,
        ]);

        return response()->json(['message' => 'Account updated successfully', 'data' => $account], 200);
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        $account->delete();
        return response()->json(['message' => 'Account deleted successfully'], 200);
    }
}
