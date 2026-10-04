<?php

namespace App\Http\Controllers;

use App\Models\SiteIncharge;
use Illuminate\Http\Request;

class SiteInchargeController extends Controller
{
    public function index(Request $request)
    {
        $query = SiteIncharge::with('site')->orderBy('created_at', 'desc');

        if ($request->has('site_id') && !empty($request->site_id)) {
            $query->where('site_id', $request->site_id);
        }
        
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('date', $request->date);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('debit_account', 'like', "%{$search}%")
                  ->orWhereHas('site', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('all')) {
            $records = $query->get();
        } else {
            $perPage = $request->get('per_page', 10);
            $records = $query->paginate($perPage);
        }

        return response()->json($records);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'site_id' => 'nullable|exists:sites,id',
            'name' => 'required|string|max:255',
            'opening_bal' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'debit_account' => 'nullable|string',
            'exp' => 'nullable|numeric',
        ]);

        $opening_bal = $validated['opening_bal'] ?? 0;
        $credit = $validated['credit'] ?? 0;
        $exp = $validated['exp'] ?? 0;
        
        $balance = $opening_bal + $credit - $exp;

        $siteIncharge = SiteIncharge::create(array_merge($validated, [
            'opening_bal' => $opening_bal,
            'credit' => $credit,
            'exp' => $exp,
            'balance' => $balance,
        ]));

        return response()->json($siteIncharge->load('site'), 201);
    }

    public function show(SiteIncharge $siteIncharge)
    {
        return response()->json($siteIncharge->load('site'));
    }

    public function update(Request $request, SiteIncharge $siteIncharge)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'site_id' => 'nullable|exists:sites,id',
            'name' => 'required|string|max:255',
            'opening_bal' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'debit_account' => 'nullable|string',
            'exp' => 'nullable|numeric',
        ]);

        $opening_bal = $validated['opening_bal'] ?? 0;
        $credit = $validated['credit'] ?? 0;
        $exp = $validated['exp'] ?? 0;
        
        $balance = $opening_bal + $credit - $exp;

        $siteIncharge->update(array_merge($validated, [
            'opening_bal' => $opening_bal,
            'credit' => $credit,
            'exp' => $exp,
            'balance' => $balance,
        ]));

        return response()->json($siteIncharge->load('site'));
    }

    public function destroy(SiteIncharge $siteIncharge)
    {
        $siteIncharge->delete();
        return response()->json(null, 204);
    }
}
