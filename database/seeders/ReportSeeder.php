<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;

class ReportSeeder extends Seeder
{
    public function run()
    {
        Report::create([
            'clarification' => 'Besoin d\'informations supplémentaires.',
            'description' => 'Route fortement endommagée avec plusieurs nids-de-poule. Difficulté pour les véhicules à passer.',
            'anomalie' => 'Trous dans la voirie',
            'status' => 'in-progress',
            'photos' => json_encode([
                'https://lecourrier-dalgerie.com/wp-content/uploads/2021/10/vehiculep5.jpg',
                'https://elwatan-dz.com/storage/15189/TRANSPORT.png',
                'https://images.pexels.com/photos/15733202/pexels-photo-15733202.jpeg',
                'https://www.algerie-eco.com/wp-content/uploads/2021/07/plastique.jpg'
            ]),
            'user_id' => 1,
            'location_id' => 1
        ]);
        Report::create([
            'clarification' => 'Signalisation à revoir.',
            'description' => 'Absence de signalisation sur une route très fréquentée. Danger pour les conducteurs.',
            'anomalie' => 'Signalisation routière défectueuse',
            'status' => 'resolved',
            'photos' => json_encode([
                'https://elwatan-dz.com/storage/15189/TRANSPORT.png',
                'https://images.pexels.com/photos/15733202/pexels-photo-15733202.jpeg',
                'https://lecourrier-dalgerie.com/wp-content/uploads/2021/10/vehiculep5.jpg',
                'https://www.algerie-eco.com/wp-content/uploads/2021/07/plastique.jpg'
            ]),
            'user_id' => 2,
            'location_id' => 2
        ]);
        Report::create([
            'clarification' => 'Aucune action immédiate nécessaire.',
            'description' => 'Érosion des berges causant des glissements de terrain.',
            'anomalie' => 'Érosion des berges',
            'status' => 'in-progress',
            'photos' => json_encode([
                'https://www.algerie-eco.com/wp-content/uploads/2021/07/plastique.jpg',
                'https://images.pexels.com/photos/15733202/pexels-photo-15733202.jpeg',
                'https://elwatan-dz.com/storage/15189/TRANSPORT.png',
                'https://lecourrier-dalgerie.com/wp-content/uploads/2021/10/vehiculep5.jpg'
            ]),
            'user_id' => 3,
            'location_id' => 3
        ]);
        Report::create([
            'clarification' => '',
            'description' => 'Débordement des égouts après fortes pluies.',
            'anomalie' => 'Débordement des égouts',
            'status' => 'received',
            'photos' => json_encode([]),
            'user_id' => 4,
            'location_id' => 4
        ]);
    }
}
