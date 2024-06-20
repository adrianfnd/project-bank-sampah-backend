<?php

namespace App\Http\Controllers\API;

use App\Models\WasteCollection;
use App\Models\Waste;
use App\Models\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HistoryController extends Controller
{
    public function wasteCollectionHistoryCostumer(Request $request)
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
    
            $month = $request->input('month');
            $year = $request->input('year');
    
            $wasteCollections = WasteCollection::where('user_id', $user->id)
                ->whereMonth('collection_date', $month)
                ->whereYear('collection_date', $year)
                ->orderBy('collection_date', 'desc')
                ->get();
    
            $data = $wasteCollections->map(function($collection) {
                $wastes = Waste::where('waste_collection_id', $collection->id)->get();
                
                return [
                    'date' => $collection->collection_date,
                    'description' => $collection->description,
                    'weight_total' => $wastes->sum('weight'),
                    'point_total' => $wastes->sum('point'),
                    'confirmation_status' => ucwords(str_replace('_', ' ', $collection->confirmation_status)),
                    'details' => [
                        'Organic' => $wastes->where('category', 'organic')->sum('weight'),
                        'Non Organic' => $wastes->where('category', 'non_organic')->sum('weight'),
                        'B3' => $wastes->where('category', 'b3')->sum('weight'),
                        'Other' => $wastes->whereNotIn('category', ['organic', 'non_organic', 'b3'])->sum('weight'),
                    ],
                ];
            });
    
            return response()->json([
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    

    public function pointRedemptionHistoryCostumer(Request $request)
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

            $month = $request->input('month');
            $year = $request->input('year');

            $transactions = Transaction::where('user_id', $user->id)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('transaction_type', 'pembayaran_tagihan')
                ->orderBy('created_at', 'desc')
                ->with(['ppobPayment', 'xenditLog'])
                ->get();

            $data = $transactions->map(function($transaction) {
                return [
                    'date' => $transaction->created_at->format('d M Y'),
                    'type' => $transaction->transaction_type,
                    'total_balance_involved' => $transaction->total_balance_involved,
                    'status' => 'Berhasil',
                    'ppob_payment' => $transaction->ppobPayment ? [
                        'customer_id' => $transaction->ppobPayment->customer_id,
                        'customer_name' => $transaction->ppobPayment->customer_name,
                        'tariff' => $transaction->ppobPayment->tariff,
                        'usage' => $transaction->ppobPayment->usage,
                        'bill' => $transaction->ppobPayment->bill,
                        'admin_fee' => $transaction->ppobPayment->admin_fee,
                    ] : null,
                ];
            });

            return response()->json([
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
      'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
   
    public function wasteCollectionHistoryStaff()
    {
        try {
            $user = Auth::user();
    
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }
    
            $wasteCollections = WasteCollection::orderBy('collection_date', 'desc')->get();
    
            $data = $wasteCollections->map(function($collection) {
                $wastes = Waste::where('waste_collection_id', $collection->id)->get();
                
                return [
                    'date' => $collection->collection_date,
                    'description' => $collection->description,
                    'weight_total' => $wastes->sum('weight'),
                    'point_total' => $wastes->sum('point'),
                    'confirmation_status' => ucwords(str_replace('_', ' ', $collection->confirmation_status)),
                    'details' => [
                        'Organic' => $wastes->where('category', 'organic')->sum('weight'),
                        'Non Organic' => $wastes->where('category', 'non_organic')->sum('weight'),
                        'B3' => $wastes->where('category', 'b3')->sum('weight'),
                        'Other' => $wastes->whereNotIn('category', ['organic', 'non_organic', 'b3'])->sum('weight'),
                    ],
                ];
            });
    
            return response()->json([
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function pointRedemptionHistoryStaff()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }

            $transactions = Transaction::where('transaction_type', 'pembayaran_tagihan')
                ->orderBy('created_at', 'desc')
                ->with(['ppobPayment','xenditLog'])
                ->get();

            $data = $transactions->map(function($transaction) {
                return [
                    'date' => $transaction->created_at->format('d M Y'),
                    'type' => $transaction->transaction_type,
                    'total_balance_involved' => $transaction->total_balance_involved,
                    'status' => 'Berhasil',
                    'ppob_payment' => $transaction->ppobPayment ? [
                        'customer_id' => $transaction->ppobPayment->customer_id,
                        'customer_name' => $transaction->ppobPayment->customer_name,
                        'tariff' => $transaction->ppobPayment->tariff,
                        'usage' => $transaction->ppobPayment->usage,
                        'bill' => $transaction->ppobPayment->bill,
                        'admin_fee' => $transaction->ppobPayment->admin_fee,
                    ] : null,
                ];
            });

            return response()->json([
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
