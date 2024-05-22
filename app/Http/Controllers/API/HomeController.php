<?php

namespace App\Http\Controllers\API;

use App\Models\WasteCollection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();
    
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }
    
            $organik = WasteCollection::where('user_id', $user->id)
                ->whereHas('waste', function($query) {
                    $query->where('category', 'Organic');
                })
                ->select('weight_total as waste_weight', 'collection_date')
                ->orderBy('collection_date', 'desc')
                ->first();

            $nonOrganic = WasteCollection::where('user_id', $user->id)
                ->whereHas('waste', function($query) {
                    $query->where('category', 'Non-Organic');
                })
                ->select('weight_total as waste_weight', 'collection_date')
                ->orderBy('collection_date', 'desc')
                ->first();

            $b3 = WasteCollection::where('user_id', $user->id)
                ->whereHas('waste', function($query) {
                    $query->where('category', 'B3');
                })
                ->select('weight_total as waste_weight', 'collection_date')
                ->orderBy('collection_date', 'desc')
                ->first();

            $totalPoint = WasteCollection::where('user_id', $user->id)->sum('point_total');
    
            return response()->json([
                'data' => [
                    'total_point' => $totalPoint,
                    'waste_collections' => [
                        'organic' => $organik,
                        'non_organic' => $nonOrganic,
                        'b3' => $b3,
                    ],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
}
