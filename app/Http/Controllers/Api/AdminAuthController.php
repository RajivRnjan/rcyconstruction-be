<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], 401);
        }

        // Generate Sanctum token
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'admin' => $admin,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user('admin')->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        $admin = $request->user('admin');

        if (!Hash::check($request->current_password, $admin->password)) {
            return response()->json([
                'message' => 'Current password does not match.'
            ], 400);
        }

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return response()->json([
            'message' => 'Password changed successfully.'
        ]);
    }
}
