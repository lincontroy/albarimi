<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StarlinkPackage;
use App\Models\StarlinkSubscription;

class StarlinkPackagesSeeder extends Seeder
{
    public function run(): void
    {
        // Check if there are already packages
        if (StarlinkPackage::count() > 0) {
            $this->command->info('Starlink packages already exist. Skipping seeder.');
            return;
        }

        // Create default packages
        $packages = [];
        foreach (StarlinkPackage::$defaultPackages as $packageData) {
            $packages[] = StarlinkPackage::create($packageData);
        }

        $this->command->info(count($packages) . ' Starlink packages created successfully.');
    }
}