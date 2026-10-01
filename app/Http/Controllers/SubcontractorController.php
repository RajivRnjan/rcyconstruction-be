<?php

namespace App\Http\Controllers;

use App\Models\Subcontractor;
use Illuminate\Http\Request;

class SubcontractorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $standalone = Subcontractor::with('site')->get()->map(function ($sub) {
            $sub->source = 'Standalone';
            return $sub;
        });

        $daily = \App\Models\DailyReportSubcontractor::with('report.site')->get()->map(function ($sub) {
            return [
                'id' => 'dr_' . $sub->id,
                'date' => $sub->report->date ?? null,
                'site' => $sub->report && $sub->report->site ? $sub->report->site : null,
                'name' => $sub->name,
                'no_of_labour' => $sub->no_of_labour,
                'work_details' => $sub->work_details,
                'source' => 'Daily Report'
            ];
        });

        $merged = collect($standalone)->merge($daily)->sortByDesc('date')->values();
        
        return response()->json($merged);
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
            'no_of_labour' => 'nullable|integer',
            'work_details' => 'nullable|string',
        ]);

        $subcontractor = Subcontractor::create($validated);

        return response()->json($subcontractor->load('site'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subcontractor $subcontractor)
    {
        return response()->json($subcontractor->load('site'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subcontractor $subcontractor)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'site_id' => 'required|exists:sites,id',
            'name' => 'required|string|max:255',
            'no_of_labour' => 'nullable|integer',
            'work_details' => 'nullable|string',
        ]);

        $subcontractor->update($validated);

        return response()->json($subcontractor->load('site'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subcontractor $subcontractor)
    {
        $subcontractor->delete();
        return response()->json(null, 204);
    }
}
