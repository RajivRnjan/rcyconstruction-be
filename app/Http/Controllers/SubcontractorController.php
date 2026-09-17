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
        return response()->json(Subcontractor::with('project')->orderBy('created_at', 'desc')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'project_id' => 'required|exists:master_sheets,id',
            'name' => 'required|string|max:255',
            'no_of_labour' => 'nullable|integer',
            'work_details' => 'nullable|string',
        ]);

        $subcontractor = Subcontractor::create($validated);

        return response()->json($subcontractor->load('project'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subcontractor $subcontractor)
    {
        return response()->json($subcontractor->load('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subcontractor $subcontractor)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'project_id' => 'required|exists:master_sheets,id',
            'name' => 'required|string|max:255',
            'no_of_labour' => 'nullable|integer',
            'work_details' => 'nullable|string',
        ]);

        $subcontractor->update($validated);

        return response()->json($subcontractor->load('project'));
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
