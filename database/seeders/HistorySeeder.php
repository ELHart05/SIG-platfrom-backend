<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\History;

class HistorySeeder extends Seeder
{
    public function run()
    {
        History::create([
            'report_id' => 1,
            'status_from' => 'received',
            'status_to' => 'in-progress',
            'clarification' => 'Début des travaux.'
        ]);
        History::create([
            'report_id' => 1,
            'status_from' => 'in-progress',
            'status_to' => 'in-progress',
            'clarification' => 'Difficulté d\'accès à la zone.'
        ]);
        History::create([
            'report_id' => 1,
            'status_from' => 'in-progress',
            'status_to' => 'in-progress',
            'clarification' => 'Problème temporaire résolu.'
        ]);
        History::create([
            'report_id' => 2,
            'status_from' => 'received',
            'status_to' => 'in-progress',
            'clarification' => 'Travaux en cours.'
        ]);
        History::create([
            'report_id' => 2,
            'status_from' => 'in-progress',
            'status_to' => 'resolved',
            'clarification' => 'Problème résolu.'
        ]);
        History::create([
            'report_id' => 3,
            'status_from' => 'received',
            'status_to' => 'in-progress',
            'clarification' => 'Inspection en cours.'
        ]);
        History::create([
            'report_id' => 3,
            'status_from' => 'in-progress',
            'status_to' => 'in-progress',
            'clarification' => 'Attente d\'une solution technique.'
        ]);
    }
}
