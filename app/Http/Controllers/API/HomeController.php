<?php

namespace App\Http\Controllers\API;

use App\Models\WasteCollection;
use App\Models\WasteCategory;
use App\Models\WasteBank;
use App\Models\Waste;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    
            $wasteCategories = WasteCategory::all()->pluck('type', 'id');
            $wasteCollections = [];
    
            $wasteData = Waste::join('waste_collections', 'wastes.waste_collection_id', '=', 'waste_collections.id')
                ->where('waste_collections.user_id', $user->id)
                ->whereMonth('waste_collections.collection_date', $month)
                ->whereYear('waste_collections.collection_date', $year)
                ->select(
                    'waste_collections.id as collection_id',
                    'waste_collections.collection_date',
                    'wastes.category_id',
                    DB::raw('SUM(wastes.weight) as total_weight'),
                    DB::raw('SUM(wastes.point) as total_point')
                )
                ->groupBy('waste_collections.id', 'waste_collections.collection_date', 'wastes.category_id')
                ->get();
    
            foreach ($wasteData as $waste) {
                $categoryType = $wasteCategories[$waste->category_id];
                if (!isset($wasteCollections[$categoryType])) {
                    $wasteCollections[$categoryType] = [
                        'waste_weight' => 0,
                        'waste_point' => 0,
                        'collection_date' => $waste->collection_date
                    ];
                }
                $wasteCollections[$categoryType]['waste_weight'] += $waste->total_weight;
                $wasteCollections[$categoryType]['waste_point'] += $waste->total_point;
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
