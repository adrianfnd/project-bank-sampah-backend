<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class NasabahController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = User::whereHas('role', function ($query) {
                $query->where('name', 'costumer');
            })->select('id', 'name', 'email', 'phone_number')
              ->orderBy('created_at', 'desc');

            if ($request->has('name')) {
                $searchTerm = $request->query('name');
                $query->where('name', 'like', '%' . $searchTerm . '%');
            }

            $nasabah = $query->get();

            $filteredNasabah = $nasabah->map(function ($user) {
                $filteredData = [
                    'id' => $user->id,
                    'name' => $user->name,
                ];

                if ($user->email) {
                    $filteredData['email'] = $user->email;
                }

                if ($user->phone_number) {
                    $filteredData['phone_number'] = $user->phone_number;
                }

                return $filteredData;
            });

            return response()->json([
                'success' => true,
                'data' => $filteredNasabah,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'email' => 'required_without:phone_number|string|email|max:255|unique:users',
                'phone_number' => 'required_without:email|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $user = User::create([
                'id' => rand(10000, 99999),
                'name' => $request->name,
                'address' => $request->address,
                'email' => $request->email ?? null,
                'phone_number' => $request->phone_number ?? null,
                'password' => bcrypt('12345678'),
                'role_id' => 3,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nasabah added successfully',
                'data' => [
                    'user' => $user,
                ],
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
