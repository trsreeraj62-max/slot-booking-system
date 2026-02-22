<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Store;
use App\Models\Service;
use App\Models\TimeSlot;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_booking_flow()
    {
        $this->withoutExceptionHandling();
        // 1. Setup Data
        $user = User::factory()->create();
        $store = Store::create([
            'id' => Str::uuid(),
            'name' => 'Test Store',
            'location' => 'Test Location',
            'status' => 'active',
            'working_days' => ['Monday'],
            'created_by' => $user->id
        ]);
        $service = Service::create([
            'id' => Str::uuid(),
            'store_id' => $store->id,
            'name' => 'Test Service',
            'duration_minutes' => 30,
            'price' => 100,
            'status' => 'active'
        ]);
        $slot = TimeSlot::create([
            'id' => Str::uuid(),
            'store_id' => $store->id,
            'service_id' => $service->id,
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'slot_date' => null, // Available
            'status' => 'available'
        ]);

        Sanctum::actingAs($user);

        // 2. GET /api/v1/stores (Day 4)
        $this->getJson('/api/v1/stores')
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test Store']);

        // 3. GET /api/v1/stores/{id}/services (Day 4)
        $this->getJson("/api/v1/stores/{$store->id}/services")
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test Service']);

        // 4. POST /api/v1/slots/{id}/lock (Day 6)
        $this->postJson("/api/v1/slots/{$slot->id}/lock", ['date' => '2026-03-01'])
            ->assertStatus(200)
            ->assertJson(['status' => 'locked']);

        // Verify slot is locked in DB
        $this->assertDatabaseHas('time_slots', [
            'id' => $slot->id,
            'status' => 'locked',
            'locked_by' => $user->id
        ]);

        // 5. POST /api/v1/bookings (Day 8)
        $this->postJson('/api/v1/bookings', [
            'store_id' => $store->id,
            'service_ids' => [$service->id],
            'slot_id' => $slot->id,
            'date' => '2026-03-01'
        ])
        ->assertStatus(201)
        ->assertJson(['status' => 'confirmed']);

        // Verify booking in DB and slot status = booked
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'status' => 'confirmed'
        ]);
        $this->assertDatabaseHas('time_slots', [
            'id' => $slot->id,
            'status' => 'booked'
        ]);
    }
}
