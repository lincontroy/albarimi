<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VisaCode;

class VisaCodesSeeder extends Seeder
{
    public function run(): void
    {
        // Check if there are already visa codes to avoid duplicates
        if (VisaCode::count() > 0) {
            $this->command->info('Visa codes already exist. Skipping seeder.');
            return;
        }

        // Generate sample visa codes only if table is empty
        $visaTypes = VisaCode::$visaTypes;
        $countries = VisaCode::$countries;

        $counter = 0;
        
        foreach ($visaTypes as $type => $details) {
            foreach (array_slice(array_keys($countries), 0, 3) as $country) {
                for ($i = 0; $i < 5; $i++) {
                    VisaCode::create([
                        'user_id' => null, // Add this line
                        'code' => VisaCode::generateCode(),
                        'visa_type' => $type,
                        'country' => $country,
                        'amount' => $details['price'],
                        'status' => 'available',
                        'expires_at' => now()->addDays(rand(30, 365)),
                        'visa_details' => $details,
                        'notes' => 'Sample visa code for testing',
                    ]);
                    $counter++;
                }
            }
        }
        
        $this->command->info($counter . ' visa codes generated successfully.');
    }
}