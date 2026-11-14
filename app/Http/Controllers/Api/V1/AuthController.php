<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and generate API token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        // Check if user's tenant is active
        if (!$user->tenant || !$user->tenant->is_active) {
            return response()->json([
                'message' => 'Votre compte est inactif. Veuillez contacter l\'administrateur.',
            ], 403);
        }

        // Delete old tokens for this user
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('mobile-app')->plainTextToken;

        // Load user's stores with pivot data
        $stores = $user->stores()->get()->map(function ($store) {
            return [
                'id' => $store->id,
                'name' => $store->name,
                'location' => $store->location,
                'role' => $store->pivot->role,
            ];
        });

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'tenant_name' => $user->tenant->name,
                'role' => $user->role?->name,
            ],
            'stores' => $stores,
            'token' => $token,
        ], 200);
    }

    /**
     * Logout user and revoke token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ], 200);
    }

    /**
     * Get authenticated user information
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $user = $request->user();

        $stores = $user->stores()->get()->map(function ($store) {
            return [
                'id' => $store->id,
                'name' => $store->name,
                'location' => $store->location,
                'role' => $store->pivot->role,
            ];
        });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'tenant_name' => $user->tenant->name,
                'role' => $user->role?->name,
            ],
            'stores' => $stores,
        ], 200);
    }
}
