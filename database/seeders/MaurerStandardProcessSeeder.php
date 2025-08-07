<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaurerStandard;
use App\Models\MaurerProcessingStep;

class MaurerStandardProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stepMap = MaurerProcessingStep::pluck('uuid', 'process_name')->toArray();

        $manualMap = [
            1 => 'Drying I',
            2 => 'Drying II',
            3 => 'Drying III',
            4 => 'Drying IV',
            5 => 'Drying V',
            6 => 'Smoking',
            7 => 'Cooking I',
            8 => 'Cooking II',
        ];

        foreach ($manualMap as $standardId => $stepName) {
            $standard = MaurerStandard::find($standardId);
            if ($standard && isset($stepMap[$stepName])) {
                $standard->process_step_uuid = $stepMap[$stepName];
                $standard->save();
            }
        }
    }
}