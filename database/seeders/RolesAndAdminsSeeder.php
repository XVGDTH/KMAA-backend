<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndAdminsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'formateur', 'apprenant'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $salomon = User::firstOrCreate(
            ['email' => 'salomonbationo45@gmail.com'], // TODO (Salomon) : remplace par ta vraie adresse
            ['name' => 'Salomon', 'password' => bcrypt('salomon123')] // TODO : change ce mot de passe immédiatement après le premier login
        );
        $salomon->assignRole('admin');

        // TODO (Salomon) : remplace par l'email réel du chef de projet quand tu l'auras
        $chefProjet = User::firstOrCreate(
            ['email' => ' admin@convergesas.com'],
            ['name' => 'ConvergeSAS', 'password' => bcrypt('admin123')]
        );
        $chefProjet->assignRole('admin');
    }
}