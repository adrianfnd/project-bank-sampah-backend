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
            $organik = WasteCollection::where('description', 'Organic')
                ->select('waste_weight', 'collection_date as point')
                ->orderBy('collection_date', 'desc')
                ->first();
            
            $nonOrganic = WasteCollection::where('description', 'Non-Organic')
                ->select('waste_weight', 'collection_date as point')
                ->orderBy('collection_date', 'desc')
                ->first();
            
            $b3 = WasteCollection::where('description', 'B3')
                ->select('waste_weight', 'collection_date as point')
                ->orderBy('collection_date', 'desc')
                ->first();
    
            $totalPoint = WasteCollection::sum('point');
    
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
