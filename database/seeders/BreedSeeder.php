<?php

namespace Database\Seeders;

use App\Models\Breed;
use Illuminate\Database\Seeder;

class BreedSeeder extends Seeder
{
    public function run(): void
    {
        $breeds = [
            [
                'name'                    => 'Race Peulh Soudanaise',
                'slug'                    => 'peulh-soudanaise',
                'origin'                  => 'Afrique de l\'Ouest (Sahel)',
                'description'             => 'Race à viande très appréciée pour sa résistance aux conditions climatiques difficiles. Bonne conformation bouchère, croissance rapide.',
                'avg_adult_weight_male'   => 55.00,
                'avg_adult_weight_female' => 42.00,
            ],
            [
                'name'                    => 'Race Touareg',
                'slug'                    => 'touareg',
                'origin'                  => 'Sahara et zones sub-sahariennes',
                'description'             => 'Excellente race d\'engraissement, très robuste. Réputée pour la qualité de sa viande, particulièrement tendre et goûteuse.',
                'avg_adult_weight_male'   => 65.00,
                'avg_adult_weight_female' => 50.00,
            ],
            [
                'name'                    => 'Race Djallonké',
                'slug'                    => 'djallonke',
                'origin'                  => 'Guinée / Afrique de l\'Ouest humide',
                'description'             => 'Petite race trypanotoléante, bien adaptée aux zones forestières humides. Rustique et fertile.',
                'avg_adult_weight_male'   => 35.00,
                'avg_adult_weight_female' => 28.00,
            ],
            [
                'name'                    => 'Race Bali-Bali',
                'slug'                    => 'bali-bali',
                'origin'                  => 'Niger / Mali',
                'description'             => 'Grande race à grandes oreilles tombantes, très prisée pour les cérémonies. Port majestueux.',
                'avg_adult_weight_male'   => 70.00,
                'avg_adult_weight_female' => 55.00,
            ],
        ];

        foreach ($breeds as $data) {
            Breed::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
