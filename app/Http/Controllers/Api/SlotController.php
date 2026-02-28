<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TimeSlot;
use Illuminate\Support\Facades\DB;

class SlotController extends Controller
{
    /**
     * Day 5: Slot Listing API
     */
    public function index(Request $request, $store_id)
    {
        try {
            $date = $request->query('date');

            $slots = TimeSlot::where('store_id', $store_id)
                ->where('slot_date', $date)
                ->get(['id as slot_id', 'start_time', 'end_time', 'status']);

            return response()->json($slots);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching slots',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Day 6: Slot Locking (CRITICAL)
     */
    public function lock(Request $request, $slot_id)
    {
        try {
            return DB::transaction(function () use ($slot_id, $request) {

                $slot = TimeSlot::where('id', $slot_id)
                    ->lockForUpdate()
                    ->first();

                if (!$slot || $slot->status !== 'available') {
                    return response()->json([
                        'message' => 'Slot unavailable'
                    ], 409);
                }

                $slot->update([
                    'status' => 'locked',
                    'slot_date' => $request->date,
                    'locked_by' => auth()->id(),
                    'lock_expires_at' => now()->addMinutes(10)
                ]);

                return response()->json([
                    'status' => 'locked',
                    'expires_at' => $slot->lock_expires_at
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while locking slot',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
