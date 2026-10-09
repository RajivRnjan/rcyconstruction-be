<?php

namespace App\Http\Controllers;

use App\Models\Subcontractor;
use App\Models\DailyReportSubcontractor;
use Illuminate\Http\Request;

class SubcontractorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $standaloneQuery = Subcontractor::with('site');
        $dailyQuery = DailyReportSubcontractor::with('report.site');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $standaloneQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('work_details', 'like', "%{$search}%");
            });
            $dailyQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('work_details', 'like', "%{$search}%");
            });
        }

        $standalone = $standaloneQuery->get()->map(function ($sub) {
            $sub->source = 'Standalone';
            return $sub;
        });

        $daily = $dailyQuery->get()->map(function ($sub) {
            return [
                'id' => 'dr_' . $sub->id,
                'date' => $sub->report->date ?? null,
                'site_id' => $sub->report->site_id ?? null,
                'site' => $sub->report && $sub->report->site ? $sub->report->site : null,
                'name' => $sub->name,
                'no_of_labour' => $sub->no_of_labour,
                'amount' => $sub->amount,
                'work_details' => $sub->work_details,
                'source' => 'Daily Report'
            ];
        });

        $merged = collect($standalone)->merge($daily)->sortByDesc('date')->values();
        
        if ($request->has('all')) {
            return response()->json($merged);
        }

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $merged->forPage($page, $perPage)->values(),
            $merged->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return response()->json($paginated);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'site_id' => 'required|exists:sites,id',
            'name' => 'required|string|max:255',
            'amount' => 'nullable|numeric',
            'work_details' => 'nullable|string',
        ]);

        $subcontractor = Subcontractor::create($validated);

        return response()->json($subcontractor->load('site'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function summary(Request $request)
    {
        // Get all unique names from both standalone and daily reports
        $standaloneNames = \App\Models\Subcontractor::select('name')->distinct()->pluck('name')->toArray();
        $dailyNames = \App\Models\DailyReportSubcontractor::select('name')->distinct()->pluck('name')->toArray();
        $names = array_unique(array_merge($standaloneNames, $dailyNames));
        
        $search = $request->get('search');
        if (!empty($search)) {
            $names = array_filter($names, function($name) use ($search) {
                return stripos($name, $search) !== false;
            });
        }
        
        $summary = [];
        foreach ($names as $name) {
            // Aggregate totals for this name
            $standalone = \App\Models\Subcontractor::where('name', $name)->get();
            $daily = \App\Models\DailyReportSubcontractor::where('name', $name)->get();
            
            $total_labour = $standalone->sum('no_of_labour') + $daily->sum('no_of_labour');
            $total_amount = $standalone->sum('amount') + $daily->sum('amount');
            
            $summary[] = [
                'name' => $name,
                'total_labour' => $total_labour,
                'total_amount' => $total_amount
            ];
        }
        
        // Sort by name
        usort($summary, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        if ($request->has('all')) {
            return response()->json($summary);
        }
        
        // Paginate manually
        $page = (int)$request->get('page', 1);
        $perPage = (int)$request->get('per_page', 10);
        $offset = ($page - 1) * $perPage;
        
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($summary, $offset, $perPage),
            count($summary),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return response()->json($paginated);
    }
    

    public function renameAll(Request $request, $name)
    {
        $validated = $request->validate([
            'new_name' => 'required|string|max:255'
        ]);
        $newName = $validated['new_name'];

        \App\Models\Subcontractor::where('name', $name)->update(['name' => $newName]);
        \App\Models\DailyReportSubcontractor::where('name', $name)->update(['name' => $newName]);

        return response()->json(['message' => 'Renamed successfully']);
    }

    public function deleteAll($name)
    {
        \App\Models\Subcontractor::where('name', $name)->delete();
        \App\Models\DailyReportSubcontractor::where('name', $name)->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function history(Request $request, $name)
    {
        $standaloneQuery = \App\Models\Subcontractor::with('site')->where('name', $name);
        $dailyQuery = \App\Models\DailyReportSubcontractor::with('report.site')->where('name', $name);

        $standalone = $standaloneQuery->get()->map(function ($sub) {
            $sub->source = 'Standalone';
            return $sub;
        });

        $daily = $dailyQuery->get()->map(function ($sub) {
            return [
                'id' => 'dr_' . $sub->id,
                'date' => $sub->report->date ?? null,
                'site_id' => $sub->report->site_id ?? null,
                'site' => $sub->report && $sub->report->site ? $sub->report->site : null,
                'name' => $sub->name,
                'no_of_labour' => $sub->no_of_labour,
                'amount' => $sub->amount,
                'work_details' => $sub->work_details,
                'source' => 'Daily Report'
            ];
        });

        $merged = collect($standalone)->merge($daily)->sortByDesc('date')->values();
        return response()->json($merged);
    }

    public function allHistory(Request $request)
    {
        $search = $request->get('search');
        $standaloneQuery = \App\Models\Subcontractor::with('site');
        $dailyQuery = \App\Models\DailyReportSubcontractor::with('report.site');

        if (!empty($search)) {
            $standaloneQuery->where('name', 'LIKE', "%{$search}%");
            $dailyQuery->where('name', 'LIKE', "%{$search}%");
        }

        $standalone = $standaloneQuery->get()->map(function ($sub) {
            $sub->source = 'Standalone';
            return $sub;
        });

        $daily = $dailyQuery->get()->map(function ($sub) {
            return [
                'id' => 'dr_' . $sub->id,
                'date' => $sub->report->date ?? null,
                'site_id' => $sub->report->site_id ?? null,
                'site' => $sub->report && $sub->report->site ? $sub->report->site : null,
                'name' => $sub->name,
                'no_of_labour' => $sub->no_of_labour,
                'amount' => $sub->amount,
                'work_details' => $sub->work_details,
                'source' => 'Daily Report'
            ];
        });

        $merged = collect($standalone)->merge($daily)->sortByDesc('date')->values();
        return response()->json($merged);
    }

    public function show($id)
    {
        if (str_starts_with($id, 'dr_')) {
            $realId = str_replace('dr_', '', $id);
            $drSub = DailyReportSubcontractor::with('report.site')->findOrFail($realId);
            return response()->json([
                'id' => 'dr_' . $drSub->id,
                'date' => $drSub->report->date ?? null,
                'site_id' => $drSub->report->site_id ?? null,
                'site' => $drSub->report && $drSub->report->site ? $drSub->report->site : null,
                'name' => $drSub->name,
                'no_of_labour' => $drSub->no_of_labour,
                'amount' => $drSub->amount,
                'work_details' => $drSub->work_details,
                'source' => 'Daily Report'
            ]);
        }
        
        $subcontractor = Subcontractor::findOrFail($id);
        return response()->json($subcontractor->load('site'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'site_id' => 'required|exists:sites,id',
            'name' => 'required|string|max:255',
            'no_of_labour' => 'nullable|integer',
            'amount' => 'nullable|numeric',
            'work_details' => 'nullable|string',
        ]);

        if (str_starts_with($id, 'dr_')) {
            $realId = str_replace('dr_', '', $id);
            $drSub = DailyReportSubcontractor::findOrFail($realId);
            $drSub->update([
                'name' => $validated['name'],
                'no_of_labour' => $validated['no_of_labour'] ?? 0,
                'amount' => $validated['amount'] ?? 0,
                'work_details' => $validated['work_details'],
            ]);
            
            // If they changed the site_id or date, we can't easily move it to a different daily report 
            // without complex logic. We will just update the details for now.
            
            $drSub->load('report.site');
            return response()->json([
                'id' => 'dr_' . $drSub->id,
                'date' => $drSub->report->date ?? null,
                'site_id' => $drSub->report->site_id ?? null,
                'site' => $drSub->report && $drSub->report->site ? $drSub->report->site : null,
                'name' => $drSub->name,
                'no_of_labour' => $drSub->no_of_labour,
                'amount' => $drSub->amount,
                'work_details' => $drSub->work_details,
                'source' => 'Daily Report'
            ]);
        }

        $subcontractor = Subcontractor::findOrFail($id);
        $subcontractor->update($validated);

        return response()->json($subcontractor->load('site'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (str_starts_with($id, 'dr_')) {
            $realId = str_replace('dr_', '', $id);
            DailyReportSubcontractor::findOrFail($realId)->delete();
            return response()->json(null, 204);
        }

        $subcontractor = Subcontractor::findOrFail($id);
        $subcontractor->delete();
        return response()->json(null, 204);
    }
}
