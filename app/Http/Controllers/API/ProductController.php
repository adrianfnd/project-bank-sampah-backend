<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $products = Product::all();
    
            $products->each(function ($product) use ($request) {
                $product->image_url = $request->getSchemeAndHttpHost() . Storage::url('images/products/' . $product->image);
            });
    
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

    public function show($id, Request $request)
    {
        try {
            $product = Product::findOrFail($id);

            $imageUrl = $request->getSchemeAndHttpHost() . Storage::url('images/products/' . $product->image);

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
            $imageName = time() . '_' . $request->name . '.' . $image->getClientOriginalExtension();
            $imageName = preg_replace('/[^a-zA-Z0-9_.]/', '_', $imageName);
            $path = $image->storeAs('public/images/products', $imageName);

            $product = Product::create(array_merge($request->all(), ['image' => $imageName]));

            $product->image_url = $request->getSchemeAndHttpHost() . Storage::url('images/products/' . $product->image);

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
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        try {
            $product = Product::findOrFail($id);
    
            $updateData = $request->only(['name', 'description', 'point_cost', 'stock']);
    
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $request->name . '.' . $image->getClientOriginalExtension();
                $imageName = preg_replace('/[^a-zA-Z0-9_.]/', '_', $imageName);
                $path = $image->storeAs('public/images/products', $imageName);
                Storage::delete('public/images/products/' . $product->image);
                $updateData['image'] = $imageName;
            }
    
            $product->update($updateData);
    
            $product->image_url = $request->getSchemeAndHttpHost() . Storage::url('images/products/' . $product->image);
    
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
            Storage::delete('public/images/products/' . $product->image);
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

