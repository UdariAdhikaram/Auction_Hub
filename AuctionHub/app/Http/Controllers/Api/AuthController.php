<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login and issue token with abilities based on role
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Define abilities based on role
        $abilities = $this->getAbilitiesForRole($user);

        // Create token with abilities
        $token = $user->createToken('auth-token', $abilities)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'abilities' => $abilities,
            ]
        ]);
    }

    /**
     * Get abilities based on user role
     */
    private function getAbilitiesForRole(User $user): array
    {
        switch ($user->role) {
            case 'admin':
                return ['admin:*', 'auction:manage', 'bid:place'];

            case 'vendor':
                // Only approved vendors can manage auctions
                if ($user->vendor && $user->vendor->approved_at !== null) {
                    return ['auction:manage', 'bid:place'];
                }
                return [];

            case 'bidder':
                // Only KYC verified bidders can place bids
                if ($user->kyc_verified_at !== null) {
                    return ['bid:place'];
                }
                return [];

            default:
                return [];
        }
    }

    /**
     * Logout - revoke current token only
     */
    public function logout(Request $request)
    {
        // Revoke the current access token only
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,vendor,bidder',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'deposit_balance' => 0,
        ]);

        // If vendor, create vendor record
        if ($request->role === 'vendor') {
            $user->vendor()->create([
                'store_slug' => str()->slug($request->name) . '-' . uniqid(),
                'commission_rate' => 0.05,
                'approved_at' => null, // Needs admin approval
            ]);
        }

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }
}
