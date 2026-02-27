<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Store;
use App\Models\Service;
use App\Models\TimeSlot;
use Carbon\Carbon;

class SlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stores = Store::all();
        
        if ($stores->isEmpty()) {
            $this->command->error('No stores found. Run DemoDataSeeder first.');
            return;
        }

        // We will create slots for the next 7 days starting from today
        $daysToSeed = 7;
        
        $totalSlotsCreated = 0;

        foreach ($stores as $store) {
            // Get a service associated with this store to assign to the slot if needed, or null
            $service = Service::where('store_id', $store->id)->first();
            $serviceId = $service ? $service->id : null;

            for ($i = 0; $i < $daysToSeed; $i++) {
                $currentDate = Carbon::today()->addDays($i);
                
                // Let's create slots from 09:00 to 17:00 (5 PM), each 1 hour long
                $startTime = Carbon::createFromTime(9, 0, 0);
                $endTime = Carbon::createFromTime(17, 0, 0);

                while ($startTime < $endTime) {
                    $slotStart = $startTime->format('H:i:00');
                    $startTime->addHour();
                    $slotEnd = $startTime->format('H:i:00');

                    TimeSlot::create([
                        'id' => (string) Str::uuid(),
                        'store_id' => $store->id,
                        'service_id' => $serviceId, // Setting a default service ID, depending on DB schema it could be nullable
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd,
                        'slot_date' => $currentDate->format('Y-m-d'),
                        'status' => 'available',
                    ]);

                    $totalSlotsCreated++;
                }
            }
        }
        
        $this->command->info("Successfully created $totalSlotsCreated time slots for all stores spanning the next 7 days!");
    }
}
