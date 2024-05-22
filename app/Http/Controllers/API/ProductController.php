<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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

    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);

            $imageUrl = Storage::url('images/'.$product->image);

            return response()->json([
                'data' => array_merge($product->toArray(), ['image_url' => $imageUrl]),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'point_cost' => 'required|numeric',
            'stock' => 'required|integer',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $path = $image->storeAs('public/images', $imageName);

            $product = Product::create(array_merge($request->all(), ['image' => $imageName]));

            return response()->json([
                'message' => 'Product created successfully.',
                'data' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'description' => 'string',
            'point_cost' => 'numeric',
            'stock' => 'integer',
            'image' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = Product::findOrFail($id);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time().'.'.$image->getClientOriginalExtension();
                $path = $image->storeAs('public/images', $imageName);
                Storage::delete('public/images/'.$product->image);
                $product->image = $imageName;
            }

            $product->update($request->all());

            return response()->json([
                'message' => 'Product updated successfully.',
                'data' => $product,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            Storage::delete('public/images/'.$product->image);
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
