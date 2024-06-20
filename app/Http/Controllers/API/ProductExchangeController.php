<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\ProductExchange;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductExchangeController extends Controller
{
    public function exchangeProduct(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'quantities' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $productIds = $request->input('product_ids', []);
        $quantities = $request->input('quantities', []);

        $user = Auth::user();
        $totalPoints = 0;

        foreach ($productIds as $index => $productId) {
            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['error' => 'Invalid product ID: ' . $productId], 400);
            }

            if ($product->stock < $quantities[$index]) {
                return response()->json(['error' => 'Insufficient stock for product ID: ' . $productId], 400);
            }

            $quantity = $quantities[$index] ?? 1;
            $totalPoints += $product->point_cost * $quantity;
        }

        if ($user->current_point < $totalPoints) {
            return response()->json(['error' => 'Insufficient points'], 400);
        }

        try {
            $user->loadMissing('productExchanges');

            $user->current_point -= $totalPoints;

            $currentDateTime = now();
            foreach ($productIds as $index => $productId) {
                $product = Product::find($productId);
                $quantity = $quantities[$index] ?? 1;

                $product->stock -= $quantity;
                $product->save();

                $productExchange = new ProductExchange([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'total_points' => $product->point_cost * $quantity,
                    'exchange_date' => $currentDateTime,
                    'created_by' => $user->id,
                ]);
                $user->productExchanges()->save($productExchange);
            }

            $user->save();

            return response()->json([
                'message' => 'Product exchange successful'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to exchange product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}