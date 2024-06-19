<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\WasteCollection;
use App\Models\Waste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
                'waste_id' => 'required|exists:wastes,id',
                'weight_total' => 'required|numeric',
                'point_total' => 'required|numeric',
                'collection_date' => 'required|date',
                'address' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $wasteCollection = WasteCollection::create([
                'user_id' => $validatedData['user_id'],
                'weight_total' => $validatedData['weight_total'],
                'point_total' => $validatedData['point_total'],
                'collection_date' => $validatedData['collection_date'],
                'confirmation_status' => 'menunggu_konfirmasi',
                'address' => $validatedData['address'],
                'created_by' => auth()->id(),
            ]);

            $adminUser = User::whereHas('role', function ($query) {
                $query->where('name', 'admin');
            })->first();

            if ($adminUser) {
                Notification::create([
                    'title' => 'Permintaan Penjemputan Sampah',
                    'user_id' => $adminUser->id,
                    'description' => 'Ada permintaan penjemputan sampah dari ' . $wasteCollection->user->name . ' dengan alamat ' . $wasteCollection->address,
                    'type' => 'penjemputan_sampah',
                    'status' => 'unread',
                ]);
            }

            return response()->json(['message' => 'Permintaan setor sampah berhasil dibuat'], 201);
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

            return response()->json(['message' => 'Penjemputan sampah berhasil dikonfirmasi']);
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

            if (isset($validatedData['sampah_organik'])) {
                $organicWaste = Waste::where('category', 'organik')->first();
                $wasteCollection->waste()->attach($organicWaste->id, ['weight' => $validatedData['sampah_organik']]);
            }

            if (isset($validatedData['sampah_non_organik'])) {
                $nonOrganicWaste = Waste::where('category', 'non_organik')->first();
                $wasteCollection->waste()->attach($nonOrganicWaste->id, ['weight' => $validatedData['sampah_non_organik']]);
            }

            if (isset($validatedData['sampah_b3'])) {
                $b3Waste = Waste::where('category', 'b3')->first();
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

}

