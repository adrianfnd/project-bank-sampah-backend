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
    
            $wasteCollections = WasteCollection::where('user_id', $user->id)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('confirmation_status', 'berhasil')
                ->get();
    
            $totalPoint = $wasteCollections->sum('point_total');
            $totalWeight = $wasteCollections->sum('weight_total');
    
            $lastCollection = $wasteCollections->sortByDesc('created_at')->first();
    
            $wasteCollectionsByType = [
                'anorganik' => [
                    'type' => 'anorganik',
                    'waste_weight' => $totalWeight,
                    'waste_point' => $totalPoint,
                    'collection_date' => $lastCollection ? date('Y-m-d', strtotime($lastCollection->created_at)) : null
                ]
            ];
    
            return response()->json([
                'data' => [
                    'total_point' => $totalPoint,
                    'waste_collections' => $wasteCollectionsByType
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
