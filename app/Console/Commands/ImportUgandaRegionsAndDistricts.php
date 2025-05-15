<?php

namespace App\Console\Commands;

use Database\Seeders\RegionSeeder;
use Database\Seeders\DistrictSeeder;
use Illuminate\Console\Command;

class ImportUgandaRegionsAndDistricts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:uganda-data {--regions : Import only regions} {--districts : Import only districts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Ugandan regions and districts from CSV files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $importRegions = $this->option('regions');
        $importDistricts = $this->option('districts');
        
        // If no specific option is provided, import both
        if (!$importRegions && !$importDistricts) {
            $importRegions = $importDistricts = true;
        }
        
        if ($importRegions) {
            $this->info('Importing regions...');
            (new RegionSeeder)->run();
            $this->info('Regions import completed.');
        }
        
        if ($importDistricts) {
            $this->info('Importing districts...');
            (new DistrictSeeder)->run();
            $this->info('Districts import completed.');
        }
        
        $this->info('All imports completed successfully.');
        
        return Command::SUCCESS;
    }
}
