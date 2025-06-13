<?php

namespace Database\Seeders;

use App\Models\Demande;
use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemandeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer ou créer des utilisateurs
        $users = User::factory(3)->create();
        $clients = Client::factory(5)->create();

        // Créer 20 demandes avec différents états
        Demande::factory(5)
            ->withClient()
            ->create(['createur_id' => $users->random()->id]);

        Demande::factory(8)
            ->assigned()
            ->withClient()
            ->create([
                'createur_id' => $users->random()->id,
                'receveur_id' => $users->random()->id,
            ]);

        Demande::factory(4)
            ->completed()
            ->withClient()
            ->create([
                'createur_id' => $users->random()->id,
                'receveur_id' => $users->random()->id,
            ]);

        Demande::factory(3)
            ->urgent()
            ->withClient()
            ->create([
                'createur_id' => $users->random()->id,
                'statut' => 'en_attente'
            ]);

        // Créer quelques demandes pour aujourd'hui pour tester le dashboard
        Demande::factory(2)
            ->withClient()
            ->create([
                'createur_id' => $users->random()->id,
                'date_demande' => now(),
                'created_at' => now(),
            ]);
    }
}
