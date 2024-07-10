<?php

namespace App\Http\Controllers\API;

use App\Models\WasteCollection;
use App\Models\WasteCategory;
use App\Models\WasteBank;
use App\Models\Waste;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function wasteCollection(Request $request)
    {
        try {
            $user = Auth::user();
    
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }
    
            $validator = Validator::make($request->all(), [
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:1900|max:' . date('Y'),
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            $month = $request->query('month');
            $year = $request->query('year');
    
            $wasteCategories = WasteCategory::all();
            $wasteCollections = [];
    
            foreach ($wasteCategories as $category) {
                $categoryName = $category->name;
                
                $wasteData = WasteCollection::where('user_id', $user->id)
                    ->whereMonth('collection_date', $month)
                    ->whereYear('collection_date', $year)
                    ->with(['waste' => function ($query) use ($category) {
                        $query->where('category_id', $category->id);
                    }])
                    ->get()
                    ->map(function ($collection) use ($category) {
                        $totalWeight = Waste::where('waste_collection_id', $collection->id)
                            ->where('category_id', $category->id)
                            ->sum('weight');
                        $totalPoint = Waste::where('waste_collection_id', $collection->id)
                            ->where('category_id', $category->id)
                            ->sum('point');
                        return [
                            'waste_weight' => $totalWeight,
                            'waste_point' => $totalPoint,
                            'collection_date' => $collection->collection_date,
                        ];
                    })
                    ->first();
    
                $wasteCollections[$categoryName] = $wasteData;
            }
    
            $totalPoint = WasteCollection::where('user_id', $user->id)
                ->whereMonth('collection_date', $month)
                ->whereYear('collection_date', $year)
                ->sum('point_total');
    
            return response()->json([
                'data' => [
                    'total_point' => $totalPoint,
                    'waste_collections' => $wasteCollections,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function wasteBank() 
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }

            $wasteBank = WasteBank::where('user_id', $user->id)->first();

            return response()->json([
                'waste_bank' => 'Bank Sampah ' . $wasteBank->name,
                'longitude' => $wasteBank->longitude,
                'latitude' => $wasteBank->latitude
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
