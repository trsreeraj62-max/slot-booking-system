<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Service;
use App\Models\TimeSlot;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdvancedBookingTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $store;
    private $service;
    private $slot;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->store = Store::create([
            'id' => Str::uuid(),
            'name' => 'Advanced Test Store',
            'location' => 'Test Location',
            'status' => 'active',
            'working_days' => ['Monday'],
            'created_by' => $this->user->id
        ]);
        $this->service = Service::create([
            'id' => Str::uuid(),
            'store_id' => $this->store->id,
            'name' => 'Advanced Test Service',
            'duration_minutes' => 30,
            'price' => 100,
            'status' => 'active'
        ]);
        $this->slot = TimeSlot::create([
            'id' => Str::uuid(),
            'store_id' => $this->store->id,
            'service_id' => $this->service->id,
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'slot_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'available'
        ]);
    }

    public function test_unauthorized_access()
    {
        // Try to access locked endpoint without actingAs
        $response = $this->postJson("/api/v1/slots/{$this->slot->id}/lock", ['date' => Carbon::now()->format('Y-m-d')]);
        
        $response->assertStatus(401);
    }

    public function test_expired_lock_confirmation_fails()
    {
        Sanctum::actingAs($this->user);

        // Manually lock the slot but set it to be expired
        $this->slot->update([
            'status' => 'locked',
            'slot_date' => Carbon::now()->format('Y-m-d'),
            'locked_by' => $this->user->id,
            'lock_expires_at' => Carbon::now()->subMinutes(1) // EXPIRED!
        ]);

        // Attempt to book
        $response = $this->postJson('/api/v1/bookings', [
            'store_id' => $this->store->id,
            'service_ids' => [$this->service->id],
            'slot_id' => $this->slot->id,
            'date' => Carbon::now()->format('Y-m-d')
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'Slot lock invalid or expired']);
    }

    public function test_double_booking_lock_attempt_fails()
    {
        $user2 = User::factory()->create();

        // Let user 1 lock the slot
        $this->slot->update([
            'status' => 'locked',
            'slot_date' => Carbon::now()->format('Y-m-d'),
            'locked_by' => $this->user->id,
            'lock_expires_at' => Carbon::now()->addMinutes(5)
        ]);

        // Let user 2 try to lock the exact same slot
        Sanctum::actingAs($user2);
        
        $response = $this->postJson("/api/v1/slots/{$this->slot->id}/lock", ['date' => Carbon::now()->format('Y-m-d')]);
        
        $response->assertStatus(409)
                 ->assertJsonFragment(['message' => 'Slot unavailable']);
    }

    public function test_invalid_service_selection()
    {
        Sanctum::actingAs($this->user);

        // Generate a random UUID that is not in the system for service
        $invalidServiceId = Str::uuid();

        // Lock slot standardly
        $this->slot->update([
            'status' => 'locked',
            'slot_date' => Carbon::now()->format('Y-m-d'),
            'locked_by' => $this->user->id,
            'lock_expires_at' => Carbon::now()->addMinutes(5)
        ]);

        $response = $this->postJson('/api/v1/bookings', [
            'store_id' => $this->store->id,
            'service_ids' => [(string) $invalidServiceId], // Invalid service
            'slot_id' => $this->slot->id,
            'date' => Carbon::now()->format('Y-m-d')
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['service_ids.0']);
    }
}
