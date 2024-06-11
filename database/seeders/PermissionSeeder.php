<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::updateOrCreate(['name' => 'Administrador', 'editable' => 0, 'guard_name' => 'api']);
        
        /*
            |--------------------------------------------------------------------------
            | Batterie permissions
            |--------------------------------------------------------------------------
        */
        Permission::create([
            'name' => 'batterie.list',
            'display_name' => 'Listar Baterias',
            'group' => 'Baterias',
            'group_id' => '1',
            'route' => '/batterie',
            'guard_name' => 'api'
        ])->syncRoles([$adminRole]);

        Permission::create([
            'name' => 'batterie.create',
            'display_name' => 'Crear Bateria',
            'group' => 'Baterias',
            'group_id' => '1',
            'route' => '/batterie',
            'guard_name' => 'api'
        ])->syncRoles([$adminRole]);

        Permission::create([
            'name' => 'batterie.update',
            'display_name' => 'Actualizar Bateria',
            'group' => 'Baterias',
            'group_id' => '1',
            'route' => '/batterie',
            'guard_name' => 'api'
        ])->syncRoles([$adminRole]);
        
        Permission::create([
            'name' => 'batterie.get',
            'display_name' => 'Consultar Bateria',
            'group' => 'Baterias',
            'group_id' => '1',
            'route' => '/batterie',
            'guard_name' => 'api'
        ])->syncRoles([$adminRole]);

        Permission::create([
            'name' => 'batterie.delete',
            'display_name' => 'Eliminar Bateria',
            'group' => 'Baterias',
            'group_id' => '1',
            'route' => '/batterie',
            'guard_name' => 'api'
        ])->syncRoles([$adminRole]);
        
        
        /*
            |--------------------------------------------------------------------------
            | Ovens permissions
            |--------------------------------------------------------------------------
        */
        Permission::create([
            'name' => 'oven.list',
            'display_name' => 'Listar Hornos',
            'group' => 'Hornos',
            'group_id' => null,
            'route' => '/oven',
            'guard_name' => 'api'
        ])->syncRoles([$adminRole]);

        Permission::create([
            'name' => 'oven.create',
            'display_name' => 'Crear Horno',
            'group' => 'Hornos',
            'group_id' => null,
            'route' => '/oven',
            'guard_name' => 'api'
        ])->syncRoles([$adminRole]);

        Permission::create([
            'name' => 'oven.update',
            'display_name' => 'Actualizar Horno',
            'group' => 'Hornos',
            'group_id' => null,
            'route' => '/oven',
            'guard_name' => 'api'
        ])->syncRoles([$adminRole]);
        
        Permission::create([
            'name' => 'oven.get',
            'display_name' => 'Consultar Horno',
            'group' => 'Hornos',
            'group_id' => null,
            'route' => '/oven',
            'guard_name' => 'api'
        ])->syncRoles([$adminRole]);

        Permission::create([
            'name' => 'oven.delete',
            'display_name' => 'Eliminar Horno',
            'group' => 'Hornos',
            'group_id' => null,
            'route' => '/oven',
            'guard_name' => 'api'
        ])->syncRoles([$adminRole]);

    }
}
