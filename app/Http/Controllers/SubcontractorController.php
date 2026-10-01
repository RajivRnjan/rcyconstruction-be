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
            'amount' => 'nullable|numeric',
            'work_details' => 'nullable|string',
        ]);

        if (str_starts_with($id, 'dr_')) {
            $realId = str_replace('dr_', '', $id);
            $drSub = DailyReportSubcontractor::findOrFail($realId);
            $drSub->update([
                'name' => $validated['name'],
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
