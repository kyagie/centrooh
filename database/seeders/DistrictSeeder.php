<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the districts table to start fresh
        Schema::disableForeignKeyConstraints();
        District::truncate();
        Schema::enableForeignKeyConstraints();
        
        $csvFile = public_path('csv/ugandan_districts.csv');
        
        if (!file_exists($csvFile)) {
            Log::error('CSV file not found: ' . $csvFile);
            return;
        }
        
        $csvData = array_map('str_getcsv', file($csvFile));
        $header = array_shift($csvData); // Remove header row
        
        // Process in chunks of 50 records
        $chunks = array_chunk($csvData, 50);
        
        foreach ($chunks as $chunk) {
            $records = [];
            
            foreach ($chunk as $row) {
                $data = array_combine($header, $row);
                
                $records[] = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'status' => 'active',
                    'region_id' => $data['region_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Insert the chunk of records
            DB::table('districts')->insert($records);
            
            // Log progress
            Log::info('Imported ' . count($records) . ' districts');
        }
        
        Log::info('District seeding completed');
    }
}
