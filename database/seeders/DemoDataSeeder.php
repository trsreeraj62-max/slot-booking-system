<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Store;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->command->error('Admin user not found. Please ensure the admin user exists.');
            return;
        }

        $storeNames = ['Elite Barbershop', 'Luxury Spa', 'Modern Dentals', 'Pro Fitness Gym', 'Auto Care Hub'];
        $storeDesc = ['Premium haircut and styling.', 'Relaxing massage and spa treatments.', 'Complete dental care and checkups.', 'State of the art fitness equipment.', 'Full-service auto repair and wash.'];
        $serviceNames = [
            ['Men\'s Haircut', 'Beard Trimming', 'Hair Coloring'],
            ['Deep Tissue Massage', 'Facial Treatment', 'Hot Stone Massage'],
            ['Teeth Whitening', 'General Checkup', 'Dental Cleaning'],
            ['Personal Training', 'Yoga Class', 'Day Pass'],
            ['Oil Change', 'Car Wash & Detailing', 'Tire Rotation']
        ];

        for ($i = 0; $i < 5; $i++) {
            $store = Store::create([
                'id' => (string) Str::uuid(),
                'name' => $storeNames[$i],
                'location' => '12' . $i . ' Main St, Cityville',
                'description' => $storeDesc[$i],
                'status' => 'active',
                'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'created_by' => $admin->id,
            ]);

            foreach ($serviceNames[$i] as $index => $srvName) {
                Service::create([
                    'id' => (string) Str::uuid(),
                    'store_id' => $store->id,
                    'name' => $srvName,
                    'description' => "Professional " . strtolower($srvName) . " service.",
                    'duration_minutes' => ($index + 1) * 30, // 30, 60, 90 mins
                    'price' => ($index + 1) * 20.00 + 10,   // $30, $50, $70
                    'status' => 'active',
                ]);
            }
        }
        
        $this->command->info('Successfully created 5 demo stores with 3 services each!');
    }
}
