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
        $statuses = ['رضيعه', 'فطام', 'تلقيح', 'فحص حمل', 'حايل','حامل', 'ولاده', 'علاج فوري', 'علاج','مراقبه', 'سليم'];
        foreach ($statuses as $statusName) {
            \App\Models\Status::create(['name' => $statusName]);
        }

        // Sheep
        for ($i = 1; $i <= 10; $i++) {
            $currentStatusId = rand(1, 7);
            $nextStatusId = $currentStatusId < 7 ? $currentStatusId + 1 : 1;
            DB::table('sheep')->insert([
                'code' => 'SHP' . $i,
                'breed_id' => rand(1, count($breeds)),
                'birth_date' => now()->subYears(rand(1, 3))->subMonths(rand(0, 11)),
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'health_status_id' => 1,
                'current_status_id' => $currentStatusId,
                'next_status_id' => $nextStatusId,
                'mother_id' => $i > 2 ? rand(1, $i - 1) : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // expense_frequencys
        $frequencies = ['يومي', 'اسبوعي', 'شهري', 'سنوي'];
        foreach ($frequencies as $frequencyName) {
            \App\Models\ExpenseFrequency::create(['name' => $frequencyName]);
        }
        // expense_types
        $types = ['علف', 'دواء', 'إيجار', 'رواتب', 'صيانة'];
        foreach ($types as $typeName) {
            \App\Models\ExpenseType::create([
                'name' => $typeName,
            ]);
        }
        // Expenses
        for ($i = 1; $i <= 20; $i++) {
            \App\Models\Expense::create([
                'expense_type_id' => rand(1, count($types)),
                'expense_frequency_id' => rand(1, count($frequencies)),
                'amount' => rand(100, 1000),
            ]);
        }
        // Settings
            \App\Models\Setting::create([
                'key' => 'breeding_after_birth',
                'label' => 'مدة التلقيح بعد الولادة',
                'type' => 'duration',
                'value' => '60', // 60 يوم بعد الولادة
            ]);
            \App\Models\Setting::create([
                'key' => 'breeding_after_weaning',
                'label' => 'مدة التلقيح بعد الفطام',
                'type' => 'duration',
                'value' => '180', // 180 يوم بعد الفطام
            ]);
            \App\Models\Setting::create([
                'key' => 'vaccination_cycle',
                'label' => 'دورة التلقيح',
                'type' => 'duration',
                'value' => '7', // 7 أيام
            ]);

             \App\Models\Setting::create([
                'key' => 'expected_price_under_3_months',
                'label' => 'السعر المتوقع لأقل من 3 شهور',
                'type' => 'currency',
                'value' => '100', // السعر المتوقع لأقل من 3 شهور
            ]);

             \App\Models\Setting::create([
                'key' => 'expected_price_3_to_6_months',
                'label' => 'السعر المتوقع بين 3 إلى 6 شهور',
                'type' => 'currency',
                'value' => '200', // السعر المتوقع بين 3 إلى 6 شهور
            ]);

             \App\Models\Setting::create([
                'key' => 'expected_price_over_6_months_male',
                'label' => 'السعر المتوقع لأكثر من 6 شهور - ذكر',
                'type' => 'currency',
                'value' => '300', // السعر المتوقع لأكثر من 6 شهور - ذكر
            ]);

    }
}
