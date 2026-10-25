<?php

namespace Database\Seeders;

use App\Models\Barangay;
use Illuminate\Database\Seeder;

class BarangaySeeder extends Seeder
{
    /**
     * The 17 barangays of Olongapo City.
     *
     * @var list<string>
     */
    public const array NAMES = [
        'Asinan',
        'Banicain',
        'Barretto',
        'East Bajac-Bajac',
        'East Tapinac',
        'Gordon Heights',
        'Kalaklan',
        'Mabayuan',
        'New Cabalan',
        'New Ilalim',
        'New Kababae',
        'New Kalalake',
        'Old Cabalan',
        'Pag-asa',
        'Santa Rita',
        'West Bajac-Bajac',
        'West Tapinac',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::NAMES as $name) {
            Barangay::firstOrCreate(['name' => $name]);
        }
    }
}
