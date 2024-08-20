<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run()
    {
        Location::create([
            'longitude' => 3.1683495431990707,
            'latitude' => 36.6977162052582,
            'label' => 'M5X9+976, Oued Smar, Algeria'
        ]);
        Location::create([
            'longitude' => 3.1693722340789066,
            'latitude' => 36.70521078707006,
            'label' => 'P549+6M6, Oued Smar, Algeria'
        ]);
        Location::create([
            'longitude' => 3.1668862252356007,
            'latitude' => 36.70678724723581,
            'label' => 'P566+8FX, Oued Smar, Algeria'
        ]);
        Location::create([
            'longitude' => 3.1603280310729076,
            'latitude' => 36.71463334016717,
            'label' => 'P575+RMJ, Oued Smar, Algeria'
        ]);
    }
}
