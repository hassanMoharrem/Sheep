<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Users
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'phone' => '0555555555',
            'city' => 'الرياض',
            'avatar' => 'https://ui-avatars.com/api/?name=Admin',
            'role' => 'admin',
            'password' => bcrypt('admin12345'),
        ]);

        // Breeds
        $breeds = ['نعيمي', 'سواكني', 'حري', 'نجدي'];
        foreach ($breeds as $breedName) {
            \App\Models\Breed::create(['name' => $breedName]);
        }

        // Statuses
        $statuses = ['رضيعه', 'مفطومه', 'ملقحه', 'حامل', 'والد', 'سليم', 'مريض'];
        foreach ($statuses as $statusName) {
            \App\Models\Status::create(['name' => $statusName]);
        }

        // Sheep
        for ($i = 1; $i <= 10; $i++) {
            $currentStatusId = rand(1, 5);
            $nextStatusId = $currentStatusId < 5 ? $currentStatusId + 1 : 1;
            DB::table('sheep')->insert([
                'code' => 'SHP' . $i,
                'breed_id' => rand(1, count($breeds)),
                'birth_date' => now()->subYears(rand(1, 3))->subMonths(rand(0, 11)),
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'health_status_id' => rand(6, 7), // سليم أو مريض
                'current_status_id' => $currentStatusId,
                'next_status_id' => $nextStatusId,
                'mother_id' => $i > 2 ? rand(1, $i - 1) : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // // Tasks
        // for ($i = 1; $i <= 10; $i++) {
        //     \App\Models\Task::create([
        //         'sheep_id' => $i,
        //         'action_type_id' => ['fatem', 'mating', 'pregnancy_check', 'birth'][rand(0, 3)],
        //         'status_id' => rand(1, 5),
        //         'scheduled_date' => now()->addDays(rand(1, 30)),
        //         'status' => 'pending',
        //         'result' => null,
        //     ]);
        // }
    }
}
