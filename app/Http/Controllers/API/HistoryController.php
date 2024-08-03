<?php

namespace App\Http\Controllers\API;

use App\Models\WasteCollection;
use App\Models\Waste;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\ProductExchange;
use App\Models\User;
use App\Models\WasteCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $wasteCollections->groupBy(function ($item) {
                return Carbon::parse($item->created_at)->format('Y-m-d');
            })->map(function ($collections, $date) {
                return [
                    'date' => $date,
                    'collections' => $collections->map(function ($collection) {
                        $wastes = Waste::where('waste_collection_id', $collection->id)->get();
                        
                        $details = $wastes->groupBy('category_id')->map(function($group) {
                            $category = WasteCategory::find($group->first()->category_id);
                            return [
                                'category_name' => $category ? $category->name : 'Unknown',
                                'weight' => $group->sum('weight')
                            ];
                        })->values();
                        
                        return [
                            'description' => $collection->description,
                            'weight_total' => $wastes->sum('weight'),
                            'point_total' => $wastes->sum('point'),
                            'confirmation_status' => ucwords(str_replace('_', ' ', $collection->confirmation_status)),
                            'created_at' => $collection->created_at,
                            'details' => $details,
                        ];
                    })->values()
                ];
            })->values();

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
    
            $transactionData = $transactions->map(function ($transaction) {
                return [
                    'date' => $transaction->created_at->format('d M Y'),
                    'type' => 'pembayaran_tagihan',
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
    
            $productExchanges = DB::table('product_exchanges')
                ->select(
                    'user_id',
                    'exchange_date',
                    'created_by',
                    DB::raw('SUM(total_points) as total_points'),
                    DB::raw('GROUP_CONCAT(product_id) as product_ids'),
                    DB::raw('GROUP_CONCAT(quantity) as quantities')
                )
                ->where('user_id', $user->id)
                ->whereMonth('exchange_date', $month)
                ->whereYear('exchange_date', $year)
                ->groupBy('user_id', 'exchange_date', 'created_by')
                ->orderBy('exchange_date', 'desc')
                ->get();
    
            $productExchangeData = $productExchanges->map(function ($exchange) {
                $productIds = explode(',', $exchange->product_ids);
                $quantities = explode(',', $exchange->quantities);
    
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
    
                $exchangeDetails = [];
                foreach ($productIds as $index => $productId) {
                    $exchangeDetails[] = [
                        'product_id' => $productId,
                        'product_name' => $products[$productId]->name ?? 'Unknown',
                        'quantity' => $quantities[$index],
                        'total_points' => $exchange->total_points,
                    ];
                }
    
                return [
                    'date' => (new \DateTime($exchange->exchange_date))->format('d M Y'),
                    'type' => 'penukaran_produk',
                    'total_balance_involved' => $exchange->total_points,
                    'status' => 'Berhasil',
                    'product_exchange' => $exchangeDetails,
                ];
            });
    
            $mergedData = collect($transactionData)->merge($productExchangeData)->sortByDesc('date')->values()->all();
    
            return response()->json([
                'data' => $mergedData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function wasteCollectionHistoryStaff(Request $request)
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

            $query = WasteCollection::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->orderBy('created_at', 'desc');

            if ($request->has('nama_nasabah')) {
                $query->where('name', 'like', '%' . $request->nama_nasabah . '%');
            }

            $wasteCollections = $query->get();

            $data = $wasteCollections->groupBy(function ($item) {
                return Carbon::parse($item->created_at)->format('Y-m-d');
            })->map(function ($collections, $date) {
                return [
                    'date' => $date,
                    'collections' => $collections->map(function ($collection) {
                        $wastes = Waste::where('waste_collection_id', $collection->id)->get();
                        
                        $details = $wastes->groupBy('category_id')->map(function($group) {
                            $category = WasteCategory::find($group->first()->category_id);
                            return [
                                'category_name' => $category ? $category->name : 'Unknown',
                                'weight' => $group->sum('weight')
                            ];
                        })->values();
                        
                        return [
                            'name' => $collection->name,
                            'description' => $collection->description,
                            'weight_total' => $wastes->sum('weight'),
                            'point_total' => $wastes->sum('point'),
                            'confirmation_status' => ucwords(str_replace('_', ' ', $collection->confirmation_status)),
                            'created_at' => $collection->created_at,
                            'details' => $details,
                        ];
                    })->values()
                ];
            })->values();

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

    public function pointRedemptionHistoryStaff(Request $request)
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
    
            $transactionQuery = Transaction::where('transaction_type', 'pembayaran_tagihan')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->orderBy('created_at', 'desc')
                ->with(['ppobPayment', 'xenditLog', 'user']);
    
            $productExchangeQuery = DB::table('product_exchanges')
                ->select(
                    'user_id',
                    'exchange_date',
                    'created_by',
                    DB::raw('SUM(total_points) as total_points'),
                    DB::raw('GROUP_CONCAT(product_id) as product_ids'),
                    DB::raw('GROUP_CONCAT(quantity) as quantities')
                )
                ->whereMonth('exchange_date', $month)
                ->whereYear('exchange_date', $year)
                ->groupBy('user_id', 'exchange_date', 'created_by')
                ->orderBy('exchange_date', 'desc');

            if ($request->has('nama_nasabah')) {
                $transactionQuery->whereHas('user', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->nama_nasabah . '%');
                });
    
                $productExchangeQuery->join('users', 'product_exchanges.user_id', '=', 'users.id')
                    ->where('users.name', 'like', '%' . $request->nama_nasabah . '%');
            }
    
            $transactions = $transactionQuery->get();
            $productExchanges = $productExchangeQuery->get();
    
            $transactionData = $transactions->map(function ($transaction) {
                return [
                    'date' => $transaction->created_at->format('d M Y'),
                    'name' => $transaction->user->name,
                    'type' => 'pembayaran_tagihan',
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
    
            $productExchangeData = $productExchanges->map(function ($exchange) {
                $productIds = explode(',', $exchange->product_ids);
                $quantities = explode(',', $exchange->quantities);
    
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
    
                $exchangeDetails = [];
                foreach ($productIds as $index => $productId) {
                    $exchangeDetails[] = [
                        'product_id' => $productId,
                        'product_name' => $products[$productId]->name ?? 'Unknown',
                        'quantity' => $quantities[$index],
                        'total_points' => $exchange->total_points,
                    ];
                }
    
                $user = User::find($exchange->user_id);
    
                return [
                    'date' => (new \DateTime($exchange->exchange_date))->format('d M Y'),
                    'name' => $user ? $user->name : 'Unknown',
                    'type' => 'penukaran_produk',
                    'total_balance_involved' => $exchange->total_points,
                    'status' => 'Berhasil',
                    'product_exchange' => $exchangeDetails,
                ];
            });
    
            $mergedData = collect($transactionData)->merge($productExchangeData)->sortByDesc('date')->values()->all();
    
            return response()->json([
                'data' => $mergedData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}