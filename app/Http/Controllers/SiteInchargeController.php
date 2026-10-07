<?php

namespace App\Http\Controllers;

use App\Models\SiteIncharge;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SiteInchargeController extends Controller
{
    public function index(Request $request)
    {
        $query = SiteIncharge::with(['site', 'account'])->orderBy('created_at', 'desc');

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

    
    public function summary(Request $request)
    {
        $query = SiteIncharge::query()
            ->selectRaw("
                MAX(id) as id, 
                name, 
                MAX(site_id) as site_id, 
                SUM(opening_bal) as opening_bal, 
                SUM(credit) as credit, 
                SUM(exp) + COALESCE((
                    SELECT SUM(dre.amount) 
                    FROM daily_reports dr 
                    JOIN daily_report_expenses dre ON dr.id = dre.daily_report_id 
                    WHERE dr.site_incharge = site_incharges.name
                ), 0) + COALESCE((
                    SELECT SUM(drs.amount) 
                    FROM daily_reports dr 
                    JOIN daily_report_subcontractors drs ON dr.id = drs.daily_report_id 
                    WHERE dr.site_incharge = site_incharges.name
                ), 0) as exp, 
                (SUM(opening_bal) + SUM(credit)) - (SUM(exp) + COALESCE((
                    SELECT SUM(dre.amount) 
                    FROM daily_reports dr 
                    JOIN daily_report_expenses dre ON dr.id = dre.daily_report_id 
                    WHERE dr.site_incharge = site_incharges.name
                ), 0) + COALESCE((
                    SELECT SUM(drs.amount) 
                    FROM daily_reports dr 
                    JOIN daily_report_subcontractors drs ON dr.id = drs.daily_report_id 
                    WHERE dr.site_incharge = site_incharges.name
                ), 0)) as balance
            ")
            ->groupBy("name");

        if ($request->has("search") && !empty($request->search)) {
            $search = $request->search;
            $query->where("name", "like", "%{$search}%");
        }

        if ($request->has("all")) {
            $records = $query->with("site")->get();
        } else {
            $records = $query->with("site")->paginate($request->get("per_page", 10));
        }
        return response()->json($records);
    }

    public function history($name)
    {
        // Get manual entries
        $manualRecords = SiteIncharge::with(["site", "account"])
            ->where("name", $name)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'is_manual' => true,
                    'date' => $item->date ? date('Y-m-d', strtotime($item->date)) : date('Y-m-d', strtotime($item->created_at)),
                    'created_at' => $item->created_at,
                    'opening_bal' => (float)$item->opening_bal,
                    'credit' => (float)$item->credit,
                    'exp' => (float)$item->exp,
                    'site_name' => $item->site ? $item->site->name : '-',
                    'account_name' => $item->account ? $item->account->account_details : '-',
                    'remark' => $item->remark,
                ];
            });

        // Get daily report expenses
        $reports = \App\Models\DailyReport::where('site_incharge', $name)
            ->with(['expenses', 'subcontractors', 'site'])
            ->get();
            
        $reportRecords = collect();
        foreach ($reports as $report) {
            $totalExp = $report->expenses->sum('amount');
            $totalSub = $report->subcontractors->sum('amount');
            $total = $totalExp + $totalSub;
            
            if ($total > 0) {
                $reportRecords->push([
                    'id' => 'dr_' . $report->id,
                    'is_manual' => false,
                    'date' => $report->date,
                    'created_at' => $report->created_at,
                    'opening_bal' => 0,
                    'credit' => 0,
                    'exp' => (float)$total,
                    'site_name' => $report->site ? $report->site->name : '-',
                    'account_name' => '-',
                    'remark' => '-',
                ]);
            }
        }

        // Merge, sort by date ascending, calculate running balance
        $all = $manualRecords->concat($reportRecords)
            ->sortBy(function ($item) {
                return $item['date'] . ' ' . $item['created_at'];
            })
            ->values();

        $running_balance = 0;
        $all = $all->map(function ($item) use (&$running_balance) {
            $running_balance += $item['opening_bal'] + $item['credit'] - $item['exp'];
            $item['balance'] = $running_balance;
            return $item;
        });

        // Return sorted by date descending for UI
        return response()->json($all->sortByDesc(function ($item) {
            return $item['date'] . ' ' . $item['created_at'];
        })->values());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'site_id' => 'nullable|exists:sites,id',
            'account_id' => 'nullable|exists:accounts,id',
            'name' => 'required|string|max:255',
            'opening_bal' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'debit_account' => 'nullable|string',
            'exp' => 'nullable|numeric',
            'remark' => 'nullable|string',
        ]);

        $opening_bal = $validated['opening_bal'] ?? 0;
        $credit = $validated['credit'] ?? 0;
        $exp = $validated['exp'] ?? 0;
        
        $balance = $opening_bal + $credit - $exp;

        $siteIncharge = DB::transaction(function () use ($validated, $opening_bal, $credit, $exp, $balance) {
            $siteIncharge = SiteIncharge::create(array_merge($validated, [
                'opening_bal' => $opening_bal,
                'credit' => $credit,
                'exp' => $exp,
                'balance' => $balance,
            ]));

            // If an account is selected and credit is given, it's a payment from that account
            if ($siteIncharge->account_id && $credit > 0) {
                $account = Account::find($siteIncharge->account_id);
                if ($account) {
                    $account->payment_amount += $credit;
                    $account->balance -= $credit;
                    $account->save();
                }
            }

            return $siteIncharge;
        });

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
            'account_id' => 'nullable|exists:accounts,id',
            'name' => 'required|string|max:255',
            'opening_bal' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'debit_account' => 'nullable|string',
            'exp' => 'nullable|numeric',
            'remark' => 'nullable|string',
        ]);

        $opening_bal = $validated['opening_bal'] ?? 0;
        $credit = $validated['credit'] ?? 0;
        $exp = $validated['exp'] ?? 0;
        
        $balance = $opening_bal + $credit - $exp;

        DB::transaction(function () use ($siteIncharge, $validated, $opening_bal, $credit, $exp, $balance) {
            // Revert old credit from old account
            if ($siteIncharge->account_id && $siteIncharge->credit > 0) {
                $oldAccount = Account::find($siteIncharge->account_id);
                if ($oldAccount) {
                    $oldAccount->payment_amount -= $siteIncharge->credit;
                    $oldAccount->balance += $siteIncharge->credit;
                    $oldAccount->save();
                }
            }

            $siteIncharge->update(array_merge($validated, [
                'opening_bal' => $opening_bal,
                'credit' => $credit,
                'exp' => $exp,
                'balance' => $balance,
            ]));

            // Apply new credit to new account
            if ($siteIncharge->account_id && $credit > 0) {
                $newAccount = Account::find($siteIncharge->account_id);
                if ($newAccount) {
                    $newAccount->payment_amount += $credit;
                    $newAccount->balance -= $credit;
                    $newAccount->save();
                }
            }
        });

        return response()->json($siteIncharge->load('site'));
    }

    public function destroy(SiteIncharge $siteIncharge)
    {
        DB::transaction(function () use ($siteIncharge) {
            // Revert credit from account before deleting
            if ($siteIncharge->account_id && $siteIncharge->credit > 0) {
                $account = Account::find($siteIncharge->account_id);
                if ($account) {
                    $account->payment_amount -= $siteIncharge->credit;
                    $account->balance += $siteIncharge->credit;
                    $account->save();
                }
            }
            $siteIncharge->delete();
        });
        return response()->json(null, 204);
    }
}
