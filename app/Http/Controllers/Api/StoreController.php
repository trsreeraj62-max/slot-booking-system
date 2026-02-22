<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Store;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    /**
     * Display a listing of active stores.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $stores = Store::where('status', 'active')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Stores retrieved successfully',
            'data' => $stores
        ]);
    }

    /**
     * Display the specified active store.
     *
     * @param string $store_id
     * @return JsonResponse
     */
    public function show(string $store_id): JsonResponse
    {
        $store = Store::where('id', $store_id)
            ->where('status', 'active')
            ->first();

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found or is inactive'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Store retrieved successfully',
            'data' => $store
        ]);
    }
}
