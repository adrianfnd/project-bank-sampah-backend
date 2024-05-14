<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);

            $products = Product::with('creator')->limit($limit)->get();

            return response()->json([
                'data' => $products,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
