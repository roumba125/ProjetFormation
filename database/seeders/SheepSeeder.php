<?php

namespace Database\Seeders;

use App\Models\Breed;
use App\Models\Sheep;
use App\Models\Vaccination;
use App\Models\WeightRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SheepSeeder extends Seeder
{
    public function run(): void
    {
        if (Sheep::count() > 0) {
            return;
        }

        $peulh     = Breed::where('slug', 'peulh-soudanaise')->first();
        $touareg   = Breed::where('slug', 'touareg')->first();
        $djallonke = Breed::where('slug', 'djallonke')->first();

        $sheepData = [
            [
                'name'             => 'Alpha',
                'breed_id'         => $peulh->id,
                'gender'           => 'male',
                'birth_date'       => Carbon::now()->subMonths(18),
                'current_weight'   => 62.00,
                'height'           => 78.00,
                'coat_color'       => 'Brun clair avec taches blanches',
                'health_condition' => 'excellent',
                'current_diet'     => 'Foin + Concentré + Sel minéral',
                'price'            => 85000,
                'status'           => 'available',
                'is_featured'      => true,
                'vaccinations' => [
                    ['vaccine_name' => 'Fièvre aphteuse (FMDV)',      'administered_at' => '2024-01-15', 'next_due_at' => '2025-01-15', 'administered_by' => 'Dr. Koné'],
                    ['vaccine_name' => 'Pasteurellose',               'administered_at' => '2024-03-02', 'next_due_at' => '2025-03-02', 'administered_by' => 'Dr. Koné'],
                    ['vaccine_name' => 'PPR',                         'administered_at' => '2024-05-10', 'next_due_at' => '2026-05-10', 'administered_by' => 'Dr. Koné'],
                    ['vaccine_name' => 'Déparasitage (Ivermectine)',   'administered_at' => '2024-09-20', 'next_due_at' => '2025-03-20', 'administered_by' => 'Dr. Koné'],
                ],
                'weights' => [
                    ['weight' => 20.0, 'recorded_at' => Carbon::now()->subMonths(18)->toDateString(), 'diet' => 'Lait maternel'],
                    ['weight' => 31.0, 'recorded_at' => Carbon::now()->subMonths(12)->toDateString(), 'diet' => 'Pâturage'],
                    ['weight' => 45.0, 'recorded_at' => Carbon::now()->subMonths(6)->toDateString(),  'diet' => 'Pâturage + Concentré'],
                    ['weight' => 55.0, 'recorded_at' => Carbon::now()->subMonths(3)->toDateString(),  'diet' => 'Foin + Concentré'],
                    ['weight' => 62.0, 'recorded_at' => Carbon::now()->toDateString(),                'diet' => 'Foin + Concentré + Sel minéral'],
                ],
            ],
            [
                'name'             => 'Bella',
                'breed_id'         => $touareg->id,
                'gender'           => 'female',
                'birth_date'       => Carbon::now()->subMonths(24),
                'current_weight'   => 48.00,
                'height'           => 72.00,
                'coat_color'       => 'Blanc pur',
                'health_condition' => 'excellent',
                'current_diet'     => 'Pâturage + Concentré',
                'price'            => 65000,
                'status'           => 'available',
                'is_featured'      => true,
                'vaccinations' => [
                    ['vaccine_name' => 'Fièvre aphteuse (FMDV)', 'administered_at' => '2024-02-10', 'next_due_at' => '2025-02-10', 'administered_by' => 'Dr. Diallo'],
                    ['vaccine_name' => 'PPR',                    'administered_at' => '2024-04-15', 'next_due_at' => '2026-04-15', 'administered_by' => 'Dr. Diallo'],
                    ['vaccine_name' => 'Charbon bactéridien',    'administered_at' => '2024-06-01', 'next_due_at' => '2025-06-01', 'administered_by' => 'Dr. Diallo'],
                ],
                'weights' => [
                    ['weight' => 22.0, 'recorded_at' => Carbon::now()->subMonths(24)->toDateString(), 'diet' => 'Lait maternel'],
                    ['weight' => 35.0, 'recorded_at' => Carbon::now()->subMonths(12)->toDateString(), 'diet' => 'Pâturage'],
                    ['weight' => 43.0, 'recorded_at' => Carbon::now()->subMonths(6)->toDateString(),  'diet' => 'Pâturage + Concentré'],
                    ['weight' => 48.0, 'recorded_at' => Carbon::now()->toDateString(),                'diet' => 'Pâturage + Concentré'],
                ],
            ],
            [
                'name'             => 'Titan',
                'breed_id'         => $djallonke->id,
                'gender'           => 'male',
                'birth_date'       => Carbon::now()->subMonths(14),
                'current_weight'   => 45.00,
                'height'           => 68.00,
                'coat_color'       => 'Noir et blanc',
                'health_condition' => 'good',
                'current_diet'     => 'Foin + Concentré',
                'price'            => 52000,
                'status'           => 'reserved',
                'is_featured'      => false,
                'vaccinations' => [
                    ['vaccine_name' => 'PPR',           'administered_at' => '2024-03-20', 'next_due_at' => '2026-03-20', 'administered_by' => 'Dr. Koné'],
                    ['vaccine_name' => 'Pasteurellose', 'administered_at' => '2024-05-05', 'next_due_at' => '2025-05-05', 'administered_by' => 'Dr. Koné'],
                ],
                'weights' => [
                    ['weight' => 18.0, 'recorded_at' => Carbon::now()->subMonths(14)->toDateString(), 'diet' => 'Lait maternel'],
                    ['weight' => 30.0, 'recorded_at' => Carbon::now()->subMonths(7)->toDateString(),  'diet' => 'Pâturage'],
                    ['weight' => 45.0, 'recorded_at' => Carbon::now()->toDateString(),                'diet' => 'Foin + Concentré'],
                ],
            ],
        ];

        foreach ($sheepData as $data) {
            $vaccinations = $data['vaccinations'];
            $weights      = $data['weights'];
            unset($data['vaccinations'], $data['weights']);

            $data['reference'] = Sheep::generateReference();
            $sheep = Sheep::create($data);

            foreach ($vaccinations as $vac) {
                $vac['sheep_id'] = $sheep->id;
                $vaccination = Vaccination::create($vac);
                $vaccination->refreshStatus();
            }

            foreach ($weights as $w) {
                $w['sheep_id'] = $sheep->id;
                WeightRecord::create($w);
            }
        }
    }
}
