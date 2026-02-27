<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Booking;
use App\Models\TimeSlot;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Day 8: Booking Creation
     */
    public function store(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'store_id' => 'required|uuid|exists:stores,id',
                'service_ids' => 'required|array',
                'service_ids.*' => 'uuid|exists:services,id',
                'slot_id' => 'required|uuid|exists:time_slots,id',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            return DB::transaction(function () use ($request) {
                $slot = TimeSlot::where('id', $request->slot_id)
                    ->lockForUpdate()
                    ->first();

                // Validate Slot Lock (Day 8 Logic)
                if (!$slot || 
                    $slot->status !== 'locked' || 
                    $slot->slot_date?->format('Y-m-d') !== $request->date || 
                    $slot->locked_by !== auth()->id() || 
                    $slot->lock_expires_at < now()
                ) {
                    return response()->json([
                        'message' => 'Slot lock invalid or expired'
                    ], 422);
                }

                // Calculate Total Price (As per Day 4 Logic reference)
                $totalPrice = Service::whereIn('id', $request->service_ids)->sum('price');

                // Day 10 Security: Unique constraint check for (store_id, start_time, slot_date)
                $exists = Booking::where('store_id', $request->store_id)
                    ->where('date', $request->date)
                    ->whereHas('slot', function($q) use ($slot) {
                        $q->where('start_time', $slot->start_time);
                    })
                    ->where('status', 'confirmed')
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'message' => 'Slot already booked'
                    ], 409);
                }

                // Create Booking
                $booking = Booking::create([
                    'user_id' => auth()->id(),
                    'store_id' => $request->store_id,
                    'service_id' => $request->service_ids[0], // Using first service for now as schema supports one
                    'slot_id' => $request->slot_id,
                    'total_price' => $totalPrice,
                    'date' => $request->date,
                    'status' => 'confirmed',
                    'confirmation_number' => 'BK-' . strtoupper(Str::random(8))
                ]);

                // Update Slot
                $slot->update([
                    'status' => 'booked',
                    'locked_by' => null,
                    'lock_expires_at' => null
                ]);

                return response()->json([
                    'booking_id' => $booking->id,
                    'confirmation_number' => $booking->confirmation_number,
                    'booking_details' => "Booking confirmed for " . $request->date . " at " . $slot->start_time,
                    'status' => 'confirmed'
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Day 8 & 6.4: Get Booking
     */
    public function show($booking_id)
    {
        try {
            $booking = Booking::with(['store', 'service', 'slot'])
                ->where('id', $booking_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$booking) {
                return response()->json([
                    'message' => 'Booking not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Day 9: Cancel Booking
     */
    public function cancel($booking_id)
    {
        try {
            return DB::transaction(function () use ($booking_id) {
                $booking = Booking::where('id', $booking_id)
                    ->where('user_id', auth()->id())
                    ->first();

                if (!$booking) {
                    return response()->json([
                        'message' => 'Booking not found'
                    ], 404);
                }

                if ($booking->status === 'cancelled') {
                    return response()->json([
                        'message' => 'Booking already cancelled'
                    ], 422);
                }

                // Change booking status
                $booking->update(['status' => 'cancelled']);

                // Slot becomes available again
                $slot = TimeSlot::find($booking->slot_id);
                if ($slot) {
                    $slot->update([
                        'status' => 'available',
                        'locked_by' => null,
                        'lock_expires_at' => null
                    ]);
                }

                return response()->json([
                    'message' => 'Booking cancelled successfully'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
