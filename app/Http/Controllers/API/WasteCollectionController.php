<?php

namespace App\Http\Controllers\API;

use App\Models\Notification;
use App\Models\User;
use App\Models\WasteCollection;
use App\Models\WasteBank;
use App\Models\Waste;
use App\Models\WasteCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WasteCollectionController extends Controller
{
    public function index()
    {
        try {
            $wasteCollections = WasteCollection::with(['user'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($item) {
                    return Carbon::parse($item->created_at)->format('Y-m-d');
                });

            $data = [];

            foreach ($wasteCollections as $date => $collections) {
                $groupedByStatus = $collections->groupBy('confirmation_status');
                $formattedCollections = [];

                foreach ($groupedByStatus as $status => $statusCollections) {
                    $formattedCollections[$status] = $statusCollections->map(function ($collection) {
                        return [
                            'id' => $collection->id,
                            'user' => $collection->user->name,
                            'address' => $collection->address,
                            'date' => $collection->collection_date,
                            'created_at' => $collection->created_at,
                            'confirmation_status' => ucwords(str_replace('_', ' ', $collection->confirmation_status)),
                        ];
                    })->values();
                }

                $data[] = [
                    'date' => $date,
                    'collections' => $formattedCollections
                ];
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
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $wasteBank = WasteBank::where('user_id', $user->id)->first();
    
            if (!$wasteBank) {
                return response()->json([
                    'message' => 'User does not have an associated waste bank',
                ], 400);
            }

            $distance = $this->calculateDistance(
                $wasteBank->latitude,
                $wasteBank->longitude,
                $request->latitude,
                $request->longitude
            );

            if ($distance > 5) {
                return response()->json([
                    'message' => 'Collection point is outside the 5km radius of your waste bank',  // Update pesan
                ], 400);
            }

            $collectionDate = date('Y-m-d H:i:s', strtotime($request->date));
    
            $wasteCollection = WasteCollection::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'name' => $user->name,
                'collection_date' => $collectionDate,
                'confirmation_status' => 'menunggu_konfirmasi',
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'created_by' => $user->id,
            ]);

            Notification::create([
                'title' => 'Permintaan Penjemputan Sampah Berhasil',
                'user_id' => $user->id,
                'description' => 'Anda berhasil mengirim permintaan penjemputan sampah, silahkan tunggu petugas mengkonfirmasi permintaan anda dan cek status penjemputan di riwayat.',
                'type' => 'penjemputan_sampah',
                'status' => 'unread',
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
    
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371.0;
    
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
    
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
        return $earthRadius * $c;
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

            Notification::create([
                'title' => 'Permintaan Penjemputan Sampah Berhasil Dikonfirmasi',
                'user_id' => $wasteCollection->user_id,
                'description' => 'Permintaan penjemputan sampah anda telah di konfirmasi oleh petugas, silahkan tunggu petugas datang untuk mengambil sampah.',
                'type' => 'penjemputan_sampah',
                'status' => 'unread',
            ]);

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
            $wasteCategories = WasteCategory::all();
            $validationRules = [];
            foreach ($wasteCategories as $category) {
                $validationRules[str_replace(' ', '_', $category->name)] = 'nullable|numeric|min:0';
            }
    
            $validator = Validator::make($request->all(), $validationRules);
    
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
    
            $totalWeight = 0;
            $totalPoints = 0;
    
            foreach ($wasteCategories as $category) {
                $amount = $request->input(str_replace(' ', '_', $category->name), 0);
                if ($amount > 0) {
                    $weight = $amount;
                    $points = $amount * $category->price_per_unit;
    
                    Waste::create([
                        'name' => $category->name,
                        'category_id' => $category->id,
                        'weight' => $weight,
                        'point' => $points,
                        'waste_collection_id' => $wasteCollection->id,
                    ]);
    
                    $totalWeight += $weight;
                    $totalPoints += $points;
                }
            }
    
            $wasteCollection->confirmation_status = 'berhasil';
            $wasteCollection->weight_total = $totalWeight;
            $wasteCollection->point_total = $totalPoints;
            $wasteCollection->save();
    
            $user = User::find($wasteCollection->user_id);
            $user->current_point += $totalPoints;
            $user->save();
    
            Notification::create([
                'title' => 'Setoran Sampah Berhasil',
                'user_id' => $wasteCollection->user_id,
                'description' => 'Setoran sampah anda telah berhasil, silahkan cek riwayat dan poin anda yang telah masuk.',
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
            $wasteCategories = WasteCategory::all();
            $validationRules = [
                'nama_nasabah' => 'required_without:email|string|max:255',
                'email' => 'required_without:nama_nasabah|string|email|max:255',
                'address' => 'required|string|max:255',
            ];
            foreach ($wasteCategories as $category) {
                $validationRules[str_replace(' ', '_', $category->name)] = 'nullable|numeric|min:0';
            }
    
            $validator = Validator::make($request->all(), $validationRules);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            $wasteCollection = new WasteCollection();
            $wasteCollection->id = Str::uuid();
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
            $totalPoints = 0;
    
            foreach ($wasteCategories as $category) {
                $amount = $request->input(str_replace(' ', '_', $category->name), 0);
                if ($amount > 0) {
                    $weight = $amount;
                    $points = $amount * $category->price_per_unit;
    
                    Waste::create([
                        'name' => $category->name,
                        'category_id' => $category->id,
                        'weight' => $weight,
                        'point' => $points,
                        'waste_collection_id' => $wasteCollection->id,
                    ]);
    
                    $totalWeight += $weight;
                    $totalPoints += $points;
                }
            }
    
            $wasteCollection->weight_total = $totalWeight;
            $wasteCollection->point_total = $totalPoints;
            $wasteCollection->save();
    
            if ($request->has('email') && isset($user)) {
                $user->current_point += $totalPoints;
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
    
    public function calculateWasteCollection(Request $request)
    {
        try {
            $wasteCategories = WasteCategory::all();
            $validationRules = [];
            foreach ($wasteCategories as $category) {
                $validationRules[$category->name] = 'nullable|numeric|min:0';
            }
    
            $validator = Validator::make($request->all(), $validationRules);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            $totalPoints = 0;
            $wasteByType = [
                'organik' => 0,
                'anorganik' => 0,
                'b3' => 0,
            ];
    
            foreach ($wasteCategories as $category) {
                $amount = $request->input($category->name, 0);
                if ($amount > 0) {
                    $points = $amount * $category->price_per_unit;
                    $totalPoints += $points;

                    if (isset($wasteByType[$category->type])) {
                        $wasteByType[$category->type] += $amount;
                    }
                }
            }
    
            return response()->json([
                'total_points' => $totalPoints,
                'waste_by_type' => $wasteByType
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function calculateWasteCollectionManual(Request $request)
    {
        try {
            $wasteCategories = WasteCategory::all();
            $validationRules = [
                'nama_nasabah' => 'required|string|max:255',
            ];
            foreach ($wasteCategories as $category) {
                $validationRules[$category->name] = 'nullable|numeric|min:0';
            }
    
            $validator = Validator::make($request->all(), $validationRules);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            $totalPoints = 0;
            $wasteByType = [
                'organik' => 0,
                'anorganik' => 0,
                'b3' => 0,
            ];
    
            foreach ($wasteCategories as $category) {
                $amount = $request->input($category->name, 0);
                if ($amount > 0) {
                    $points = $amount * $category->price_per_unit;
                    $totalPoints += $points;
    
                    if (isset($wasteByType[$category->type])) {
                        $wasteByType[$category->type] += $amount;
                    }
                }
            }
    
            return response()->json([
                'nama_nasabah' => $request->input('nama_nasabah'),
                'total_points' => $totalPoints,
                'waste_by_type' => $wasteByType
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

