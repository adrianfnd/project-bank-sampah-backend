<?php

namespace App\Http\Controllers\API;

use App\Models\Notification;
use App\Models\User;
use App\Models\WasteCollection;
use App\Models\Waste;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WasteCollectionController extends Controller
{
    public function index()
    {
        try {
            $wasteCollections = WasteCollection::with('user')
                ->get()
                ->groupBy('confirmation_status');

            $data = [];

            foreach ($wasteCollections as $status => $collections) {
                $data[$status] = $collections->map(function ($collection) {
                    return [
                        'id' => $collection->id,
                        'user' => $collection->user->name,
                        'address' => $collection->user->address,
                        'confirmation_status' => $collection->confirmation_status,
                    ];
                });
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createWasteCollection(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'address' => 'required|string',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $wasteCollection = WasteCollection::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'collection_date' => $request->collection_date,
                'confirmation_status' => 'menunggu_konfirmasi',
                'address' => $request->address,
                'created_by' => $user->id,
            ]);

            $adminUser = User::whereHas('role', function ($query) {
                $query->where('name', 'staff');
            })->first();

            if ($adminUser) {
                Notification::create([
                    'title' => 'Permintaan Penjemputan Sampah',
                    'user_id' => $adminUser->id,
                    'description' => 'Ada permintaan penjemputan sampah dari ' . $wasteCollection->user->name . ' dengan alamat ' . $wasteCollection->user->address,
                    'type' => 'penjemputan_sampah',
                    'status' => 'unread',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Waste collection created successfully',
                'data' => $wasteCollection
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function confirmWasteCollection(Request $request, $id)
    {
        try {
            $wasteCollection = WasteCollection::find($id);

            if (!$wasteCollection) {
                return response()->json(['message' => 'Waste Collection not found.'], 404);
            }

            $wasteCollection->confirmation_status = 'dikonfirmasi';
            $wasteCollection->save();

            return response()->json([
                'success' => true,
                'message' => 'Waste collection confirmed successfully',
                'data' => $wasteCollection
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function submitWasteCollection(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sampah_organik' => 'nullable|numeric',
                'sampah_non_organik' => 'nullable|numeric',
                'sampah_b3' => 'nullable|numeric',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            $validatedData = $validator->validated();
    
            $wasteCollection = WasteCollection::find($id);
    
            if (!$wasteCollection) {
                return response()->json(['message' => 'Waste Collection not found.'], 404);
            }
    
            $wasteCollection->confirmation_status = 'berhasil';
            $wasteCollection->save();
    
            // Create new Waste entries and associate them with the WasteCollection
            if (isset($validatedData['sampah_organik'])) {
                $organicWaste = Waste::create([
                    'name' => 'Organic Waste',
                    'category' => 'organic',
                    'weight' => $validatedData['sampah_organik'],
                    'point' => $this->calculatePoints($validatedData['sampah_organik']), // Assuming you have a method to calculate points
                ]);
                $wasteCollection->waste()->attach($organicWaste->id, ['weight' => $validatedData['sampah_organik']]);
            }
    
            if (isset($validatedData['sampah_non_organik'])) {
                $nonOrganicWaste = Waste::create([
                    'name' => 'Non-Organic Waste',
                    'category' => 'non_organic',
                    'weight' => $validatedData['sampah_non_organik'],
                    'point' => $this->calculatePoints($validatedData['sampah_non_organik']),
                ]);
                $wasteCollection->waste()->attach($nonOrganicWaste->id, ['weight' => $validatedData['sampah_non_organik']]);
            }
    
            if (isset($validatedData['sampah_b3'])) {
                $b3Waste = Waste::create([
                    'name' => 'B3 Waste',
                    'category' => 'b3',
                    'weight' => $validatedData['sampah_b3'],
                    'point' => $this->calculatePoints($validatedData['sampah_b3']),
                ]);
                $wasteCollection->waste()->attach($b3Waste->id, ['weight' => $validatedData['sampah_b3']]);
            }
    
            $notification = Notification::create([
                'title' => 'Penjemputan Sampah Berhasil',
                'user_id' => $wasteCollection->user_id,
                'description' => 'Penjemputan sampah Anda telah berhasil dilakukan.',
                'type' => 'penjemputan_sampah',
                'status' => 'unread',
            ]);
    
            return response()->json(['message' => 'Waste Collection submitted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    private function calculatePoints($weight)
    {
        // Implement your point calculation logic here
        return $weight * 10; // Example calculation
    }

}

