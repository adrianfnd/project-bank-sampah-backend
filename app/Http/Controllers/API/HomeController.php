<?php

namespace App\Http\Controllers\API;

use App\Models\WasteCollection;
use App\Models\WasteBank;
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

            $query = WasteCollection::where('user_id', $user->id)
                ->whereMonth('collection_date', $month)
                ->whereYear('collection_date', $year);

            $organik = (clone $query)->whereHas('waste', function ($query) {
                    $query->where('category', 'Organic');
                })
                ->select('weight_total as waste_weight', 'collection_date')
                ->orderBy('collection_date', 'desc')
                ->first();

            $nonOrganic = (clone $query)->whereHas('waste', function ($query) {
                    $query->where('category', 'Non-Organic');
                })
                ->select('weight_total as waste_weight', 'collection_date')
                ->orderBy('collection_date', 'desc')
                ->first();

            $b3 = (clone $query)->whereHas('waste', function ($query) {
                    $query->where('category', 'B3');
                })
                ->select('weight_total as waste_weight', 'collection_date')
                ->orderBy('collection_date', 'desc')
                ->first();

            $totalPoint = WasteCollection::where('user_id', $user->id)
                ->whereMonth('collection_date', $month)
                ->whereYear('collection_date', $year)
                ->sum('point_total');

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
                'waste_bank' => $wasteBank->name
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
