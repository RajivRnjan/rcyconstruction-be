<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::orderBy('created_at', 'desc');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('account_details', 'like', "%{$search}%");
        }

        if ($request->has('all')) {
            $accounts = $query->get();
        } else {
            $perPage = $request->get('per_page', 10);
            $accounts = $query->paginate($perPage);
        }

        return response()->json($accounts);
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

    public function history($id)
    {
        $account = Account::findOrFail($id);
        $transactions = collect();

        // 1. Initial Opening Balance (treated as a transaction)
        if ($account->opening_balance > 0 || $account->opening_balance < 0) {
            $transactions->push([
                'id' => 'ob_' . $account->id,
                'date' => $account->created_at->format('Y-m-d'),
                'type' => 'Opening Balance',
                'description' => 'Account Opening Balance',
                'receipt' => $account->opening_balance > 0 ? (float)$account->opening_balance : 0,
                'payment' => $account->opening_balance < 0 ? (float)abs($account->opening_balance) : 0,
                'created_at' => $account->created_at,
            ]);
        }

        // 2. Site Incharge payments (Credits)
        $siteIncharges = \App\Models\SiteIncharge::where('account_id', $id)->where('credit', '>', 0)->get();
        foreach ($siteIncharges as $si) {
            $transactions->push([
                'id' => 'si_' . $si->id,
                'date' => $si->date ? date('Y-m-d', strtotime($si->date)) : $si->created_at->format('Y-m-d'),
                'type' => 'Site Incharge Credit',
                'description' => 'Paid to: ' . $si->name . ($si->remark ? ' (' . $si->remark . ')' : ''),
                'receipt' => 0,
                'payment' => (float)$si->credit,
                'created_at' => $si->created_at,
            ]);
        }

        // 3. Head Office Incomes (Receipts)
        if (class_exists(\App\Models\HeadOfficeIncome::class)) {
            $hoIncomes = \App\Models\HeadOfficeIncome::where('account_id', $id)->get();
            foreach ($hoIncomes as $inc) {
                $transactions->push([
                    'id' => 'hoi_' . $inc->id,
                    'date' => $inc->date ? date('Y-m-d', strtotime($inc->date)) : $inc->created_at->format('Y-m-d'),
                    'type' => 'HO Income',
                    'description' => 'Received from: ' . $inc->source . ($inc->details ? ' (' . $inc->details . ')' : ''),
                    'receipt' => (float)$inc->amount,
                    'payment' => 0,
                    'created_at' => $inc->created_at,
                ]);
            }
        }

        // 4. Head Office Expenses (Payments)
        if (class_exists(\App\Models\HeadOfficeExpense::class)) {
            $hoExpenses = \App\Models\HeadOfficeExpense::where('account_id', $id)->get();
            foreach ($hoExpenses as $exp) {
                $transactions->push([
                    'id' => 'hoe_' . $exp->id,
                    'date' => $exp->date ? date('Y-m-d', strtotime($exp->date)) : $exp->created_at->format('Y-m-d'),
                    'type' => 'HO Expense',
                    'description' => 'Paid for: ' . $exp->expense_type . ($exp->details ? ' (' . $exp->details . ')' : ''),
                    'receipt' => 0,
                    'payment' => (float)$exp->amount,
                    'created_at' => $exp->created_at,
                ]);
            }
        }

        // Sort by date ascending, then calculate running balance
        $sorted = $transactions->sortBy(function ($item) {
            return $item['date'] . ' ' . $item['created_at'];
        })->values();

        $running_balance = 0;
        $sorted = $sorted->map(function ($item) use (&$running_balance) {
            $running_balance += $item['receipt'] - $item['payment'];
            $item['balance'] = $running_balance;
            return $item;
        });

        // Return descending for UI
        return response()->json($sorted->sortByDesc(function ($item) {
            return $item['date'] . ' ' . $item['created_at'];
        })->values());
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
