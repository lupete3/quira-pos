<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of clients
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $clients = Client::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => ClientResource::collection($clients),
        ], 200);
    }

    /**
     * Store a newly created client
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $client = Client::create([
            'tenant_id' => $tenantId,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'debt' => 0,
        ]);

        return response()->json([
            'message' => 'Client créé avec succès',
            'data' => new ClientResource($client),
        ], 201);
    }

    /**
     * Display the specified client
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $client = Client::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$client) {
            return response()->json([
                'message' => 'Client non trouvé',
            ], 404);
        }

        return response()->json([
            'data' => new ClientResource($client),
        ], 200);
    }

    /**
     * Update the specified client
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $client = Client::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$client) {
            return response()->json([
                'message' => 'Client non trouvé',
            ], 404);
        }

        $client->update($request->only(['name', 'phone', 'email', 'address']));

        return response()->json([
            'message' => 'Client mis à jour avec succès',
            'data' => new ClientResource($client),
        ], 200);
    }
}
