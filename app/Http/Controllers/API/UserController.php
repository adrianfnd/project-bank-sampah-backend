<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
    
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
    
        if ($user->image !== null) {
            $profileData['image_url'] = url(Storage::url('images/users/'.$user->image));
        }
    
        $profileData['current_point'] = $user->current_point;
        $profileData['created_at'] = $user->created_at;
        $profileData['updated_at'] = $user->updated_at;
    
        return response()->json([
            'success' => true,
            'data' => $profileData,
        ], 200);
    }
    
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        if ($user->email !== null) {
            $rules['email'] = 'nullable|string|email|max:255|unique:users,email,' . $user->id;
        }

        if ($user->phone_number !== null) {
            $rules['phone_number'] = 'nullable|string|max:15|unique:users,phone_number,' . $user->id;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'_'.str_replace(' ', '_', $user->name).'.'.$image->getClientOriginalExtension();
            $path = $image->storeAs('public/images/users', $imageName);

            if ($user->image) {
                Storage::delete('public/images/users/'.$user->image);
            }

            $user->image = $imageName;
        }

        $user->name = $request->name;
        $user->address = $request->address;
        
        if ($user->email !== null) {
            $user->email = $request->email;
        }

        if ($user->phone_number !== null) {
            $user->phone_number = $request->phone_number;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'address' => $user->address,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'image_url' => $user->image ? url(Storage::url('images/users/'.$user->image)) : null,
                'current_point' => $user->current_point,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $rules = [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ], 200);
    }

    
}

