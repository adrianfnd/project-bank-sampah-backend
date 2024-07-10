<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WasteBank;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\OTPMail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required_without:phone_number|string|email|max:255|unique:users',
            'phone_number' => 'required_without:email|string|max:255|unique:users',         
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }
        
        $otp = rand(100000, 999999);
    
        $user = User::create([
            'id' => rand(10000, 99999),
            'name' => $request->name,
            'address' => $request->address,
            'email' => $request->email ?? null,
            'phone_number' => $request->phone_number ?? null,
            'otp' => $otp,
            'password' => bcrypt($request->password),
            'role_id' => 3,
        ]);
    
        WasteBank::create([
            'id' => Str::uuid(),
            'name' => 'Desa Sindangpanon',
            'address' => 'Sindangpanon, Banjaran, Kabupaten Bandung, Jawa Barat',
            'user_id' => $user->id,
            'longitude' => '107.5830623',
            'latitude' => '-7.0606974',
        ]);
    
        if ($request->email) {
            Mail::to($request->email)->send(new OTPMail($otp));
        } else if ($request->phone_number) {
            // Fungsi kirim whatsapp
        }
    
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully. OTP has been sent to your email.',
            'data' => [
                'user' => $user,
            ],
        ], 201);
    }

    public function registerResendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone_number|email',
            'phone_number' => 'required_without:email|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        if ($request->email !== null) {
            $user = User::where('email', $request->email)
                        ->first();
        } elseif ($request->phone_number !== null) {
            $user = User::where('phone_number', $request->phone_number)
                        ->first();
        }
    
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
    
        $otp = rand(100000, 999999);
        $user->update(['otp' => $otp]);
    
        if ($request->email) {
            Mail::to($user->email)->send(new OTPMail($otp));
        } else if ($request->phone_number) {
            // Fungsi kirim whatsapp
        }
    
        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully',
            'data' => [
                'user' => $user,
            ],
        ], 200);
    }
    

    public function registerVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'otp' => 'required|string|min:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::find($request->id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $otp = $user->otp;

        if ($request->otp == $otp) {
            $user->update([
                'otp' => null,
                'email_verified_at' => now(),
            ]);

            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
                'error' => 'Unauthenticated',
            ], 401);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            if (Auth::user()->email_verified_at) {
                $user = Auth::user();
                $token = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'token' => $token,
                        'user' => $user,
                    ],
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not verified',
                    'error' => 'Unauthenticated',
                ], 401);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
            'error' => 'Unauthenticated',
        ], 401);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid user',
            'error' => 'Unauthenticated',
        ], 401);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $otp = rand(100000, 999999);
        $user->update(['otp' => $otp]);

        Mail::to($user->email)->send(new OTPMail($otp));

        return response()->json([
            'success' => true,
            'otp' => $otp,
            'message' => 'OTP sent to your email',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|min:6',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($user->otp !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 400);
        }

        $user->update([
            'password' => bcrypt($request->password),
            'otp' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ], 200);
    }
}

