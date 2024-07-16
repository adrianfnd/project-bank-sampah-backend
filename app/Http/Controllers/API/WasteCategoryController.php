<?php

namespace App\Http\Controllers\API;

use App\Models\WasteCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WasteCategoryController extends Controller
{
    public function list(Request $request)
    {
        try {
            $categories = WasteCategory::all()->map(function($category) {
                return [
                    'name' => $category->name,
                    'type' => ucfirst($category->type),
                    'price_per_unit' => number_format($category->price_per_unit, 0, ',', '.') . '/' . $category->unit,
                ];
            });

            return response()->json([
                'data' => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $categories = WasteCategory::limit($limit)->get();

            return response()->json([
                'data' => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = WasteCategory::findOrFail($id);

            return response()->json([
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Waste category not found.',
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:waste_categories,name',
            'price_per_unit' => 'required|numeric|min:0',
            'unit' => 'required|in:kg,piece',
            'type' => 'required|in:organic,anorganic,b3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category = WasteCategory::create($request->all());

            return response()->json([
                'message' => 'Waste category created successfully.',
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create waste category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|unique:waste_categories,name,' . $id,
            'price_per_unit' => 'numeric|min:0',
            'unit' => 'in:kg,piece',
            'type' => 'in:organic,anorganic,b3',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        try {
            $category = WasteCategory::findOrFail($id);
            $category->update($request->all());
    
            return response()->json([
                'message' => 'Waste category updated successfully.',
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update waste category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = WasteCategory::findOrFail($id);
            $category->delete();

            return response()->json([
                'message' => 'Waste category deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete waste category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
