<?php

namespace App\Http\Controllers\API;

use App\Models\WasteCategory;
use App\Models\Waste;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WasteCategoryController extends Controller
{
    public function list(Request $request)
    {
        try {
            $categories = WasteCategory::where('is_visible', true)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($category) {
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
            $categories = WasteCategory::where('is_visible', true)
                ->orderBy('created_at', 'desc')
                ->get();
    
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
            $categoryData = array_merge($request->all(), ['is_visible' => true]);
            $category = WasteCategory::create($categoryData);
    
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

            $updateData = array_merge($request->all(), ['is_visible' => true]);
            $category->update($updateData);
    
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
            $hasRelations = Waste::where('category_id', $id)->exists();

            if ($hasRelations) {
                $category->update(['is_visible' => false]);

                return response()->json([
                    'message' => 'Waste category hidden successfully.',
                    'is_fully_deleted' => false
                ], 200);
            } else {
                $category->delete();

                return response()->json([
                    'message' => 'Waste category deleted successfully.',
                    'is_fully_deleted' => true
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete waste category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

