<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
    
        $profileData = [
            'id' => $user->id,
            'name' => $user->name,
            'address' => $user->address,
        ];
    
        if ($user->email !== null) {
            $profileData['email'] = $user->email;
        }
    
        if ($user->phone_number !== null) {
            $profileData['phone_number'] = $user->phone_number;
        }
    
        $profileData['created_at'] = $user->created_at;
        $profileData['updated_at'] = $user->updated_at;
    
        return response()->json([
            'success' => true,
            'data' => $profileData,
        ], 200);
    }
    
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'password' => 'sometimes|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = Auth::user();
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user,
        ], 200);
    }
}
