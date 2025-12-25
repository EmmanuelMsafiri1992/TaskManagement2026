<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class MalawianHolidaysSeeder extends Seeder
{
    /**
     * Malawian Public Holidays
     */
    public function run(): void
    {
        $holidays = [
            // 2024 Holidays
            ['name' => "New Year's Day", 'date' => '2024-01-01', 'description' => 'Celebration of the new year', 'is_recurring' => true],
            ['name' => 'John Chilembwe Day', 'date' => '2024-01-15', 'description' => 'Honors John Chilembwe, a Baptist minister who led an uprising against colonial rule in 1915', 'is_recurring' => true],
            ['name' => "Martyrs' Day", 'date' => '2024-03-03', 'description' => 'Commemorates those who died fighting for independence', 'is_recurring' => true],
            ['name' => 'Good Friday', 'date' => '2024-03-29', 'description' => 'Christian holiday commemorating the crucifixion of Jesus Christ', 'is_recurring' => false],
            ['name' => 'Easter Monday', 'date' => '2024-04-01', 'description' => 'Day after Easter Sunday', 'is_recurring' => false],
            ['name' => 'Labour Day', 'date' => '2024-05-01', 'description' => 'International Workers Day', 'is_recurring' => true],
            ['name' => 'Kamuzu Day', 'date' => '2024-05-14', 'description' => 'Birthday of Dr. Hastings Kamuzu Banda, the first President of Malawi', 'is_recurring' => true],
            ['name' => 'Independence Day', 'date' => '2024-07-06', 'description' => 'Celebrates Malawi independence from Britain in 1964', 'is_recurring' => true],
            ['name' => "Mother's Day", 'date' => '2024-10-14', 'description' => 'Celebrates motherhood (Second Monday of October)', 'is_recurring' => false],
            ['name' => 'Christmas Day', 'date' => '2024-12-25', 'description' => 'Celebration of the birth of Jesus Christ', 'is_recurring' => true],
            ['name' => 'Boxing Day', 'date' => '2024-12-26', 'description' => 'Day after Christmas', 'is_recurring' => true],

            // 2025 Holidays
            ['name' => "New Year's Day", 'date' => '2025-01-01', 'description' => 'Celebration of the new year', 'is_recurring' => true],
            ['name' => 'John Chilembwe Day', 'date' => '2025-01-15', 'description' => 'Honors John Chilembwe, a Baptist minister who led an uprising against colonial rule in 1915', 'is_recurring' => true],
            ['name' => "Martyrs' Day", 'date' => '2025-03-03', 'description' => 'Commemorates those who died fighting for independence', 'is_recurring' => true],
            ['name' => 'Good Friday', 'date' => '2025-04-18', 'description' => 'Christian holiday commemorating the crucifixion of Jesus Christ', 'is_recurring' => false],
            ['name' => 'Easter Monday', 'date' => '2025-04-21', 'description' => 'Day after Easter Sunday', 'is_recurring' => false],
            ['name' => 'Labour Day', 'date' => '2025-05-01', 'description' => 'International Workers Day', 'is_recurring' => true],
            ['name' => 'Kamuzu Day', 'date' => '2025-05-14', 'description' => 'Birthday of Dr. Hastings Kamuzu Banda, the first President of Malawi', 'is_recurring' => true],
            ['name' => 'Independence Day', 'date' => '2025-07-06', 'description' => 'Celebrates Malawi independence from Britain in 1964', 'is_recurring' => true],
            ['name' => "Mother's Day", 'date' => '2025-10-13', 'description' => 'Celebrates motherhood (Second Monday of October)', 'is_recurring' => false],
            ['name' => 'Christmas Day', 'date' => '2025-12-25', 'description' => 'Celebration of the birth of Jesus Christ', 'is_recurring' => true],
            ['name' => 'Boxing Day', 'date' => '2025-12-26', 'description' => 'Day after Christmas', 'is_recurring' => true],

            // 2026 Holidays
            ['name' => "New Year's Day", 'date' => '2026-01-01', 'description' => 'Celebration of the new year', 'is_recurring' => true],
            ['name' => 'John Chilembwe Day', 'date' => '2026-01-15', 'description' => 'Honors John Chilembwe, a Baptist minister who led an uprising against colonial rule in 1915', 'is_recurring' => true],
            ['name' => "Martyrs' Day", 'date' => '2026-03-03', 'description' => 'Commemorates those who died fighting for independence', 'is_recurring' => true],
            ['name' => 'Good Friday', 'date' => '2026-04-03', 'description' => 'Christian holiday commemorating the crucifixion of Jesus Christ', 'is_recurring' => false],
            ['name' => 'Easter Monday', 'date' => '2026-04-06', 'description' => 'Day after Easter Sunday', 'is_recurring' => false],
            ['name' => 'Labour Day', 'date' => '2026-05-01', 'description' => 'International Workers Day', 'is_recurring' => true],
            ['name' => 'Kamuzu Day', 'date' => '2026-05-14', 'description' => 'Birthday of Dr. Hastings Kamuzu Banda, the first President of Malawi', 'is_recurring' => true],
            ['name' => 'Independence Day', 'date' => '2026-07-06', 'description' => 'Celebrates Malawi independence from Britain in 1964', 'is_recurring' => true],
            ['name' => "Mother's Day", 'date' => '2026-10-12', 'description' => 'Celebrates motherhood (Second Monday of October)', 'is_recurring' => false],
            ['name' => 'Christmas Day', 'date' => '2026-12-25', 'description' => 'Celebration of the birth of Jesus Christ', 'is_recurring' => true],
            ['name' => 'Boxing Day', 'date' => '2026-12-26', 'description' => 'Day after Christmas', 'is_recurring' => true],
        ];

        // Get first admin user or null
        $adminId = \App\Models\User::first()?->id;

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                [
                    'description' => $holiday['description'],
                    'is_recurring' => $holiday['is_recurring'],
                    'created_by' => $adminId,
                ]
            );
        }

        $this->command->info('Malawian holidays seeded successfully!');
    }
}
