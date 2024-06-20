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
                        'address' => $collection->address,
                        'confirmation_status' =>  ucwords(str_replace('_', ' ', $collection->confirmation_status)),
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
                'name' => $user->name,
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
                    'description' => 'Ada permintaan penjemputan sampah dari ' . $wasteCollection->user->name . ' dengan alamat ' . $wasteCollection->address,
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
            $wasteCollection = WasteCollection::where('id', $id)
                            ->where('confirmation_status', 'menunggu_konfirmasi')
                            ->first();

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
                'total_point' => 'required|numeric',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            $wasteCollection = WasteCollection::where('id', $id)
                            ->where('confirmation_status', 'dikonfirmasi')
                            ->first();
    
            if (!$wasteCollection) {
                return response()->json(['message' => 'Waste Collection not found.'], 404);
            }
    
            $wasteCollection->confirmation_status = 'berhasil';
            $wasteCollection->weight_total = $request->input('sampah_organik') + $request->input('sampah_non_organik') + $request->input('sampah_b3');
            $wasteCollection->point_total = $request->input('total_point');
            $wasteCollection->save();
    
            $totalWeight = 0;
            $weights = [];
    
            if ($request->has('sampah_organik')) {
                $weights['organic'] = $request->input('sampah_organik');
                $totalWeight += $request->input('sampah_organik');
            }
    
            if ($request->has('sampah_non_organik')) {
                $weights['non_organic'] = $request->input('sampah_non_organik');
                $totalWeight += $request->input('sampah_non_organik');
            }
    
            if ($request->has('sampah_b3')) {
                $weights['b3'] = $request->input('sampah_b3');
                $totalWeight += $request->input('sampah_b3');
            }
    
            if ($totalWeight > 0) {
                $pointsPerWeight = $request->input('total_point') / $totalWeight;
    
                if (isset($weights['organic'])) {
                    $organicWaste = Waste::create([
                        'name' => 'Organic Waste',
                        'category' => 'organic',
                        'weight' => $weights['organic'],
                        'point' => $pointsPerWeight * $weights['organic'],
                        'waste_collection_id' => $wasteCollection->id,
                    ]);
                }
    
                if (isset($weights['non_organic'])) {
                    $nonOrganicWaste = Waste::create([
                        'name' => 'Non-Organic Waste',
                        'category' => 'non_organic',
                        'weight' => $weights['non_organic'],
                        'point' => $pointsPerWeight * $weights['non_organic'],
                        'waste_collection_id' => $wasteCollection->id,
                    ]);
                }
    
                if (isset($weights['b3'])) {
                    $b3Waste = Waste::create([
                        'name' => 'B3 Waste',
                        'category' => 'b3',
                        'weight' => $weights['b3'],
                        'point' => $pointsPerWeight * $weights['b3'],
                        'waste_collection_id' => $wasteCollection->id,
                    ]);
                }
            }

            $user = User::find($wasteCollection->user_id);
            
            $user->current_point += $request->input('total_point');
            $user->save();
    
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

    public function submitWasteCollectionManual(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_nasabah' => 'required_without:email|string|max:255',
                'email' => 'required_without:nama_nasabah|string|email|max:255',
                'address' => 'required|string|max:255',
                'sampah_organik' => 'nullable|numeric',
                'sampah_non_organik' => 'nullable|numeric',
                'sampah_b3' => 'nullable|numeric',
                'total_point' => 'required|numeric',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            $wasteCollection = new WasteCollection();
            $wasteCollection->id = Str::uuid();
            $wasteCollection->weight_total = $request->input('sampah_organik') + $request->input('sampah_non_organik') + $request->input('sampah_b3');
            $wasteCollection->point_total = $request->input('total_point');
            $wasteCollection->address = $request->input('address');
            $wasteCollection->collection_date = now();
            $wasteCollection->confirmation_status = 'berhasil';
            $wasteCollection->created_by = Auth::user()->id;
    
            if ($request->has('nama_nasabah')) {
                $wasteCollection->name = $request->input('nama_nasabah');
            }
    
            if ($request->has('email')) {
                $user = User::where('email', $request->input('email'))->first();
                if ($user) {
                    $wasteCollection->user_id = $user->id;
                    $wasteCollection->name = $user->name;
                } else {
                    return response()->json(['message' => 'User not found.'], 404);
                }
            }
    
            $wasteCollection->save();
    
            $totalWeight = 0;
            $weights = [];
    
            if ($request->has('sampah_organik')) {
                $weights['organic'] = $request->input('sampah_organik');
                $totalWeight += $request->input('sampah_organik');
            }
    
            if ($request->has('sampah_non_organik')) {
                $weights['non_organic'] = $request->input('sampah_non_organik');
                $totalWeight += $request->input('sampah_non_organik');
            }
    
            if ($request->has('sampah_b3')) {
                $weights['b3'] = $request->input('sampah_b3');
                $totalWeight += $request->input('sampah_b3');
            }
    
            if ($totalWeight > 0) {
                $pointsPerWeight = $request->input('total_point') / $totalWeight;
    
                if (isset($weights['organic'])) {
                    Waste::create([
                        'name' => 'Organic Waste',
                        'category' => 'organic',
                        'weight' => $weights['organic'],
                        'point' => $pointsPerWeight * $weights['organic'],
                        'waste_collection_id' => $wasteCollection->id,
                    ]);
                }
    
                if (isset($weights['non_organic'])) {
                    Waste::create([
                        'name' => 'Non-Organic Waste',
                        'category' => 'non_organic',
                        'weight' => $weights['non_organic'],
                        'point' => $pointsPerWeight * $weights['non_organic'],
                        'waste_collection_id' => $wasteCollection->id,
                    ]);
                }
    
                if (isset($weights['b3'])) {
                    Waste::create([
                        'name' => 'B3 Waste',
                        'category' => 'b3',
                        'weight' => $weights['b3'],
                        'point' => $pointsPerWeight * $weights['b3'],
                        'waste_collection_id' => $wasteCollection->id,
                    ]);
                }
            }
    
            if ($request->has('email')) {
                $user->current_point += $request->input('total_point');
                $user->save();
            }
    
            return response()->json(['message' => 'Waste Collection created successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}

