<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'first_name' => 'Abdelkader',
            'last_name' => 'BENZID',
            'email' => 'abdelkader.benzid@esi-sba.dz',
            'phone' => '+213660123456'
        ]);
        User::create([
            'first_name' => 'Khaled',
            'last_name' => 'BOUSSAD',
            'email' => 'khaled.boussad@esi-sba.dz',
            'phone' => '+213669789456'
        ]);
        User::create([
            'first_name' => 'Sofia',
            'last_name' => 'BELLAL',
            'email' => 'sofia.bellal@esi-sba.dz',
            'phone' => '+213551789632'
        ]);
        User::create([
            'first_name' => 'Amine',
            'last_name' => 'ZAIDI',
            'email' => 'amine.zaidi@esi-sba.dz',
            'phone' => '+213550123987'
        ]);
    }
}
