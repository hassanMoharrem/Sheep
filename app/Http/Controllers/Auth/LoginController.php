<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\FirebaseService;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($request->filled('fcm_token')) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function loginWithPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        try {
            $auth = app(FirebaseService::class)->getAuth();
            $verifiedIdToken = $auth->verifyIdToken($request->id_token);
            $phoneNumber = $verifiedIdToken->claims()->get('phone_number');
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid Firebase ID token',
            ], 401);
        }

        if (!$phoneNumber) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => 'Phone number not found in token',
            ], 400);
        }

        $normalized = trim($phoneNumber);
        $variants = [$normalized];
        if (str_starts_with($normalized, '+')) {
            $variants[] = ltrim($normalized, '+');
        } else {
            $variants[] = '+' . $normalized;
        }

        $user = User::whereIn('phone', $variants)->first();

        if (!$user) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'User not found for this phone number',
            ], 404);
        }

        if ($request->filled('fcm_token')) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->fcm_token = null;
            $user->save();
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }
}