<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        return response()->json(Material::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $material = Material::create($validated);
        return response()->json($material, 201);
    }

    public function show(Material $material)
    {
        return response()->json($material);
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $material->update($validated);
        return response()->json($material);
    }

    public function destroy(Material $material)
    {
        $material->delete();
        return response()->json(['message' => 'Material deleted successfully']);
    }
}
