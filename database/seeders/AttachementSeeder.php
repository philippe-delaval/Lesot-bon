<?php

namespace Database\Seeders;

use App\Models\Attachement;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttachementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Gérer le cas où $this->command peut être null (dans les tests)
        if ($this->command) {
            $this->command->info('🚀 Génération des attachements de travaux...');
        }

        // S'assurer qu'on a des utilisateurs et clients
        if (User::count() === 0) {
            if ($this->command) {
                $this->command->warn('Aucun utilisateur trouvé. Création d\'un utilisateur de test...');
            }
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@lesot.com',
            ]);
        }

        // Création de clients variés si nécessaire
        if (Client::count() < 10) {
            if ($this->command) {
                $this->command->info('Création de clients supplémentaires...');
            }
            Client::factory(5)->individual()->create();
            Client::factory(3)->company()->create();
            Client::factory(2)->withHistory()->create();
        }

        // Désactiver temporairement les contraintes de clés étrangères pour de meilleures performances
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } else {
            DB::statement('PRAGMA foreign_keys=OFF');
        }

        try {
            if ($this->command) {
                $this->command->info('📋 Génération des attachements...');

                // Attachements normaux
                $this->command->withProgressBar(35, function () {
                    Attachement::factory(35)->create();
                });

                $this->command->newLine();
                $this->command->info('🕒 Génération des attachements récents...');

                // Attachements récents (7 derniers jours)
                $this->command->withProgressBar(8, function () {
                    Attachement::factory(8)->recent()->create();
                });

                $this->command->newLine();
                $this->command->info('⚠️ Génération des attachements d\'urgence...');

                // Attachements d'urgence
                $this->command->withProgressBar(3, function () {
                    Attachement::factory(3)->emergency()->create();
                });

                $this->command->newLine();
                $this->command->info('📝 Génération des attachements avec beaucoup de fournitures...');

                // Attachements avec beaucoup de fournitures
                $this->command->withProgressBar(2, function () {
                    Attachement::factory(2)->withManySupplies()->create();
                });

                $this->command->newLine();
                $this->command->info('❌ Génération des attachements sans signature client...');

                // Attachements sans signature client
                $this->command->withProgressBar(2, function () {
                    Attachement::factory(2)->withoutClientSignature()->create();
                });
            } else {
                // Mode test : génération directe sans progress bar
                Attachement::factory(35)->create();
                Attachement::factory(8)->recent()->create();
                Attachement::factory(3)->emergency()->create();
                Attachement::factory(2)->withManySupplies()->create();
                Attachement::factory(2)->withoutClientSignature()->create();
            }

        } finally {
            // Réactiver les contraintes de clés étrangères
            if (config('database.default') === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } else {
                DB::statement('PRAGMA foreign_keys=ON');
            }
        }

        if ($this->command) {
            $this->command->newLine();
            $this->command->info('📊 Génération terminée !');
            
            // Statistiques finales
            $total = Attachement::count();
            $recent = Attachement::where('created_at', '>=', now()->subDays(7))->count();
            $withoutSignature = Attachement::whereNull('nom_signataire_client')->count();
            
            $this->command->table(
                ['Métrique', 'Valeur'],
                [
                    ['Total attachements', $total],
                    ['Attachements récents (7j)', $recent],
                    ['Sans signature client', $withoutSignature],
                    ['Clients uniques', Attachement::distinct('client_id')->count()],
                    ['Utilisateurs créateurs', Attachement::distinct('created_by')->count()],
                ]
            );

            $this->command->info('✅ Seeder AttachementSeeder terminé avec succès !');
        }
    }

    /**
     * Génération rapide pour les tests de performance
     */
    public function performance(int $count = 1000): void
    {
        $this->command->info("🏃 Mode performance : génération de {$count} attachements...");
        
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } else {
            DB::statement('PRAGMA foreign_keys=OFF');
        }
        
        try {
            // Utiliser des clients existants pour optimiser les performances
            $clientIds = Client::pluck('id')->take(20)->toArray();
            $userIds = User::pluck('id')->take(5)->toArray();
            
            if (empty($clientIds) || empty($userIds)) {
                $this->command->error('Pas assez de clients ou d\'utilisateurs pour le mode performance');
                return;
            }

            $chunkSize = 100;
            $chunks = ceil($count / $chunkSize);
            
            for ($i = 0; $i < $chunks; $i++) {
                $remaining = min($chunkSize, $count - ($i * $chunkSize));
                
                $this->command->withProgressBar($remaining, function () use ($remaining, $clientIds, $userIds) {
                    Attachement::factory($remaining)
                        ->state(function (array $attributes) use ($clientIds, $userIds) {
                            return [
                                'client_id' => fake()->randomElement($clientIds),
                                'created_by' => fake()->randomElement($userIds),
                            ];
                        })
                        ->create();
                });
                
                $this->command->newLine();
                $this->command->info("Chunk " . ($i + 1) . "/{$chunks} terminé");
            }
            
        } finally {
            if (config('database.default') === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } else {
                DB::statement('PRAGMA foreign_keys=ON');
            }
        }

        $this->command->info("✅ {$count} attachements générés avec succès !");
    }
}
