<?php

namespace App\Http\Controllers\API;

use App\Models\WasteCollection;
use App\Models\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HistoryController extends Controller
{
    public function wasteCollectionHistory(Request $request)
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
                ->with(['waste'])
                ->orderBy('collection_date', 'desc')
                ->get();

            $data = $wasteCollections->map(function($collection) {
                return [
                    'date' => $collection->collection_date,
                    'description' => $collection->description,
                    'weight_total' => $collection->weight_total,
                    'point_total' => $collection->point_total,
                    'confirmation_status' => ucwords(str_replace('_', ' ', $collection->confirmation_status)),
                    'details' => [
                        'Organic' => $collection->waste->where('category', 'Organic')->sum('weight'),
                        'Non Organic' => $collection->waste->where('category', 'Non-Organic')->sum('weight'),
                        'B3' => $collection->waste->where('category', 'B3')->sum('weight'),
                        'Other' => $collection->waste->whereNotIn('category', ['Organic', 'Non-Organic', 'B3'])->sum('weight'),
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

    public function pointRedemptionHistory(Request $request)
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
}
