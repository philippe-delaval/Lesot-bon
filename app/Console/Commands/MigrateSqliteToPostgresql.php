<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Client;
use App\Models\Attachement;

class MigrateSqliteToPostgresql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:migrate-sqlite-to-pgsql 
                           {--dry-run : Simulation sans modification}
                           {--chunk-size=100 : Taille des lots pour la migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrer les données de SQLite vers PostgreSQL';

    private $sqliteConnection;
    private $postgresConnection;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk-size');

        $this->info('🔄 Migration SQLite vers PostgreSQL');
        $this->info('===================================');

        if ($dryRun) {
            $this->warn('Mode simulation activé - aucune donnée ne sera modifiée');
        }

        // Vérifier les connexions
        if (!$this->checkConnections()) {
            return 1;
        }

        // Vérifier que PostgreSQL est vide ou demander confirmation
        if (!$this->confirmMigration()) {
            return 1;
        }

        try {
            // Désactiver les contraintes temporairement
            if (!$dryRun) {
                $this->disableForeignKeyChecks();
            }

            // Migrer les données
            $this->migrateUsers($chunkSize, $dryRun);
            $this->migrateClients($chunkSize, $dryRun);
            $this->migrateAttachements($chunkSize, $dryRun);

            // Réactiver les contraintes
            if (!$dryRun) {
                $this->enableForeignKeyChecks();
                $this->updateSequences();
            }

            $this->info('✅ Migration terminée avec succès!');
            
            if (!$dryRun) {
                $this->displayMigrationSummary();
            }

        } catch (\Exception $e) {
            $this->error('❌ Erreur durant la migration: ' . $e->getMessage());
            
            if (!$dryRun) {
                $this->warn('⚠️  Nettoyage recommandé de la base PostgreSQL');
            }
            
            return 1;
        }

        return 0;
    }

    /**
     * Vérifier les connexions aux bases de données
     */
    private function checkConnections(): bool
    {
        try {
            // Vérifier SQLite
            $this->sqliteConnection = DB::connection('sqlite');
            $this->sqliteConnection->getPdo();
            $this->info('✓ Connexion SQLite OK');

            // Vérifier PostgreSQL
            $this->postgresConnection = DB::connection('pgsql');
            $this->postgresConnection->getPdo();
            $this->info('✓ Connexion PostgreSQL OK');

            return true;

        } catch (\Exception $e) {
            $this->error('❌ Erreur de connexion: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Confirmer la migration si PostgreSQL contient des données
     */
    private function confirmMigration(): bool
    {
        $tables = ['users', 'clients', 'attachements'];
        $hasData = false;

        foreach ($tables as $table) {
            if (Schema::connection('pgsql')->hasTable($table)) {
                $count = $this->postgresConnection->table($table)->count();
                if ($count > 0) {
                    $this->warn("La table PostgreSQL '$table' contient $count enregistrements");
                    $hasData = true;
                }
            }
        }

        if ($hasData) {
            return $this->confirm('Continuer la migration ? Les données existantes seront écrasées.');
        }

        return true;
    }

    /**
     * Migrer les utilisateurs
     */
    private function migrateUsers(int $chunkSize, bool $dryRun): void
    {
        $this->info('📊 Migration des utilisateurs...');

        $totalUsers = $this->sqliteConnection->table('users')->count();
        $this->line("Total à migrer: $totalUsers utilisateurs");

        if ($totalUsers === 0) {
            $this->warn('Aucun utilisateur à migrer');
            return;
        }

        if (!$dryRun) {
            // Vider la table cible
            $this->postgresConnection->table('users')->delete();
        }

        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        $this->sqliteConnection->table('users')
            ->orderBy('id')
            ->chunk($chunkSize, function ($users) use ($dryRun, $bar) {
                if (!$dryRun) {
                    $userData = $users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'email_verified_at' => $user->email_verified_at,
                            'password' => $user->password,
                            'remember_token' => $user->remember_token,
                            'created_at' => $user->created_at,
                            'updated_at' => $user->updated_at,
                        ];
                    })->toArray();

                    $this->postgresConnection->table('users')->insert($userData);
                }
                
                $bar->advance(count($users));
            });

        $bar->finish();
        $this->newLine();
        $this->info("✓ Migration des utilisateurs terminée");
    }

    /**
     * Migrer les clients
     */
    private function migrateClients(int $chunkSize, bool $dryRun): void
    {
        $this->info('👥 Migration des clients...');

        if (!Schema::connection('sqlite')->hasTable('clients')) {
            $this->warn('Table clients non trouvée dans SQLite');
            return;
        }

        $totalClients = $this->sqliteConnection->table('clients')->count();
        $this->line("Total à migrer: $totalClients clients");

        if ($totalClients === 0) {
            $this->warn('Aucun client à migrer');
            return;
        }

        if (!$dryRun) {
            $this->postgresConnection->table('clients')->delete();
        }

        $bar = $this->output->createProgressBar($totalClients);
        $bar->start();

        $this->sqliteConnection->table('clients')
            ->orderBy('id')
            ->chunk($chunkSize, function ($clients) use ($dryRun, $bar) {
                if (!$dryRun) {
                    $clientData = $clients->map(function ($client) {
                        return [
                            'id' => $client->id,
                            'nom' => $client->nom,
                            'email' => $client->email,
                            'adresse' => $client->adresse,
                            'complement_adresse' => $client->complement_adresse,
                            'code_postal' => $client->code_postal,
                            'ville' => $client->ville,
                            'telephone' => $client->telephone,
                            'notes' => $client->notes,
                            'created_at' => $client->created_at,
                            'updated_at' => $client->updated_at,
                        ];
                    })->toArray();

                    $this->postgresConnection->table('clients')->insert($clientData);
                }
                
                $bar->advance(count($clients));
            });

        $bar->finish();
        $this->newLine();
        $this->info("✓ Migration des clients terminée");
    }

    /**
     * Migrer les attachements
     */
    private function migrateAttachements(int $chunkSize, bool $dryRun): void
    {
        $this->info('📎 Migration des attachements...');

        $totalAttachements = $this->sqliteConnection->table('attachements')->count();
        $this->line("Total à migrer: $totalAttachements attachements");

        if ($totalAttachements === 0) {
            $this->warn('Aucun attachement à migrer');
            return;
        }

        if (!$dryRun) {
            $this->postgresConnection->table('attachements')->delete();
        }

        $bar = $this->output->createProgressBar($totalAttachements);
        $bar->start();

        $this->sqliteConnection->table('attachements')
            ->orderBy('id')
            ->chunk($chunkSize, function ($attachements) use ($dryRun, $bar) {
                if (!$dryRun) {
                    $attachementData = $attachements->map(function ($attachement) {
                        return [
                            'id' => $attachement->id,
                            'client_id' => $attachement->client_id,
                            'numero_dossier' => $attachement->numero_dossier,
                            'client_nom' => $attachement->client_nom,
                            'client_email' => $attachement->client_email,
                            'client_adresse_facturation' => $attachement->client_adresse_facturation,
                            'lieu_intervention' => $attachement->lieu_intervention,
                            'date_intervention' => $attachement->date_intervention,
                            'designation_travaux' => $attachement->designation_travaux,
                            'fournitures_travaux' => $attachement->fournitures_travaux,
                            'temps_total_passe' => $attachement->temps_total_passe,
                            'signature_entreprise_path' => $attachement->signature_entreprise_path,
                            'signature_client_path' => $attachement->signature_client_path,
                            'pdf_path' => $attachement->pdf_path,
                            'geolocation' => $attachement->geolocation,
                            'created_by' => $attachement->created_by,
                            'created_at' => $attachement->created_at,
                            'updated_at' => $attachement->updated_at,
                        ];
                    })->toArray();

                    $this->postgresConnection->table('attachements')->insert($attachementData);
                }
                
                $bar->advance(count($attachements));
            });

        $bar->finish();
        $this->newLine();
        $this->info("✓ Migration des attachements terminée");
    }

    /**
     * Désactiver les vérifications de clés étrangères
     */
    private function disableForeignKeyChecks(): void
    {
        $this->postgresConnection->statement('SET FOREIGN_KEY_CHECKS=0');
    }

    /**
     * Réactiver les vérifications de clés étrangères
     */
    private function enableForeignKeyChecks(): void
    {
        $this->postgresConnection->statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Mettre à jour les séquences PostgreSQL
     */
    private function updateSequences(): void
    {
        $this->info('🔧 Mise à jour des séquences PostgreSQL...');

        $sequences = [
            'users' => 'users_id_seq',
            'clients' => 'clients_id_seq',
            'attachements' => 'attachements_id_seq',
        ];

        foreach ($sequences as $table => $sequence) {
            try {
                $maxId = $this->postgresConnection->table($table)->max('id') ?? 0;
                $this->postgresConnection->statement("SELECT setval('$sequence', $maxId)");
                $this->line("✓ Séquence $sequence mise à jour (max: $maxId)");
            } catch (\Exception $e) {
                $this->warn("⚠️  Erreur mise à jour séquence $sequence: " . $e->getMessage());
            }
        }
    }

    /**
     * Afficher le résumé de la migration
     */
    private function displayMigrationSummary(): void
    {
        $this->newLine();
        $this->info('📊 Résumé de la migration:');
        $this->table(
            ['Table', 'Enregistrements'],
            [
                ['users', $this->postgresConnection->table('users')->count()],
                ['clients', $this->postgresConnection->table('clients')->count()],
                ['attachements', $this->postgresConnection->table('attachements')->count()],
            ]
        );
    }
}