<?php

namespace App\Http\Controllers\API;

use App\Models\Faq;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    public function index()
    {
        try {
            $faqs = Faq::all();

            return response()->json([
                'data' => $faqs
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
            $faq = Faq::findOrFail($id);

            return response()->json([
                'data' => $faq
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'FAQ not found.',
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $faq = Faq::create($request->all());

            return response()->json([
                'message' => 'FAQ created successfully.',
                'data' => $faq,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create FAQ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'sometimes|required|string|max:255',
            'answer' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $faq = Faq::findOrFail($id);

            $faq->update($request->all());

            return response()->json([
                'message' => 'FAQ updated successfully.',
                'data' => $faq,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update FAQ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $faq = Faq::findOrFail($id);

            $faq->delete();

            return response()->json([
                'message' => 'FAQ deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete FAQ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
