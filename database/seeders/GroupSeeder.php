<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Group;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        /*
            |--------------------------------------------------------------------------
            | Groups menu
            |--------------------------------------------------------------------------
        */
        Group::create([
            'name' => 'configuration',
            'icon' => 'settings',
            'label' => 'Configuración',
            'order_number' => '1',
        ]);

        Group::create([
            'name' => 'administration',
            'icon' => 'work',
            'label' => 'Administración',
            'order_number' => '2',
        ]);

        Group::create([
            'name' => 'tickets',
            'icon' => 'confirmation_number',
            'label' => 'Tickets',
            'order_number' => '3',
        ]);

        Group::create([
            'name' => 'reports',
            'icon' => 'summarize',
            'label' => 'Reportes',
            'order_number' => '4',
        ]);

      
    }
}
