<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Utilisateur de base pour les tests
        User::factory()->create([
            'name' => 'Admin Lesot',
            'email' => 'admin@lesot.com',
        ]);

        // Appel des seeders spécialisés
        $this->call([
            AttachementSeeder::class,
            EmployeSeeder::class,
            EquipeSeeder::class,
            PlanningSeeder::class,
        ]);
    }
}
