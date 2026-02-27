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
        try {
            $stores = Store::where('status', 'active')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Stores retrieved successfully',
                'data' => $stores
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching stores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified active store.
     *
     * @param string $store_id
     * @return JsonResponse
     */
    public function show(string $store_id): JsonResponse
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the store',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Day 11: Create Store (Admin)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
                'working_days' => 'required|array',
                'images' => 'nullable|array',
            ]);

            $validated['created_by'] = auth()->id();

            $store = Store::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Store created successfully',
                'data' => $store
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the store',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Day 11: Update Store (Admin)
     */
    public function update(Request $request, string $store_id): JsonResponse
    {
        try {
            $store = Store::findOrFail($store_id);

            $validated = $request->validate([
                'name' => 'string|max:255',
                'location' => 'string|max:255',
                'description' => 'nullable|string',
                'working_days' => 'array',
                'images' => 'nullable|array',
            ]);

            $store->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Store updated successfully',
                'data' => $store
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the store',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Day 11: Activate/Deactivate Store (Admin)
     */
    public function updateStatus(Request $request, string $store_id): JsonResponse
    {
        try {
            $store = Store::findOrFail($store_id);

            $validated = $request->validate([
                'status' => 'required|in:active,inactive',
            ]);

            $store->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Store status updated successfully',
                'data' => $store
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the store status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
