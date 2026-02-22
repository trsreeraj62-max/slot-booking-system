<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Store;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index($store_id)
    {
        // Validate store exists
        $store = Store::where('id', $store_id)->first();

        if (!$store) {
            return response()->json([
                'message' => 'Store not found'
            ], 404);
        }

        // Get services belonging to store
        $services = Service::where('store_id', $store_id)->get();

        return response()->json($services);
    }

    /**
     * Day 11: Create Service (Admin)
     */
    public function store(Request $request, $store_id)
    {
        $store = Store::where('id', $store_id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'images' => 'nullable|array',
        ]);

        $validated['store_id'] = $store->id;

        $service = Service::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => $service
        ], 201);
    }
}
