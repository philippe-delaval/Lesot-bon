<?php

namespace App\Console\Commands;

use App\Models\Attachement;
use App\Models\Client;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurgeTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:purge-test 
                           {--force : Force la suppression sans confirmation}
                           {--keep-users : Conserver les utilisateurs}
                           {--keep-clients : Conserver les clients}
                           {--only-attachements : Supprimer seulement les attachements}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge les données de test générées par les factories et seeders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧹 Purge des données de test');

        // Vérification de sécurité
        if (app()->environment('production')) {
            $this->error('❌ Cette commande ne peut pas être exécutée en production !');
            return self::FAILURE;
        }

        // Affichage des statistiques actuelles
        $this->displayCurrentStats();

        // Confirmation
        if (!$this->option('force')) {
            if (!$this->confirm('Êtes-vous sûr de vouloir supprimer ces données ?')) {
                $this->info('Opération annulée.');
                return self::SUCCESS;
            }
        }

        $this->info('🚀 Début de la purge...');

        // Désactiver les contraintes de clés étrangères temporairement
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            $deletedCounts = [];

            // Supprimer les attachements et leurs fichiers
            $deletedCounts['attachements'] = $this->purgeAttachements();

            // Supprimer les clients si demandé
            if (!$this->option('keep-clients') && !$this->option('only-attachements')) {
                $deletedCounts['clients'] = $this->purgeClients();
            }

            // Supprimer les utilisateurs de test si demandé
            if (!$this->option('keep-users') && !$this->option('only-attachements')) {
                $deletedCounts['users'] = $this->purgeTestUsers();
            }

            // Nettoyer les fichiers orphelins
            $deletedCounts['files'] = $this->cleanupOrphanedFiles();

        } finally {
            // Réactiver les contraintes de clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->displayResults($deletedCounts);

        return self::SUCCESS;
    }

    /**
     * Affiche les statistiques actuelles
     */
    private function displayCurrentStats(): void
    {
        $attachements = Attachement::count();
        $clients = Client::count();
        $users = User::count();

        $this->table(
            ['Type', 'Nombre'],
            [
                ['Attachements', $attachements],
                ['Clients', $clients],
                ['Utilisateurs', $users],
            ]
        );
    }

    /**
     * Purge les attachements
     */
    private function purgeAttachements(): int
    {
        $this->info('📋 Suppression des attachements...');

        $attachements = Attachement::all();
        $count = $attachements->count();

        if ($count === 0) {
            $this->info('Aucun attachement à supprimer.');
            return 0;
        }

        // Supprimer les fichiers associés
        $this->withProgressBar($attachements, function ($attachement) {
            // Supprimer les fichiers de signatures
            if ($attachement->signature_entreprise_path) {
                Storage::disk('public')->delete($attachement->signature_entreprise_path);
            }
            if ($attachement->signature_client_path) {
                Storage::disk('public')->delete($attachement->signature_client_path);
            }
            // Supprimer le PDF
            if ($attachement->pdf_path) {
                Storage::disk('public')->delete($attachement->pdf_path);
            }
        });

        $this->newLine();

        // Supprimer les enregistrements
        Attachement::truncate();

        $this->info("✅ {$count} attachements supprimés");
        return $count;
    }

    /**
     * Purge les clients de test
     */
    private function purgeClients(): int
    {
        $this->info('👥 Suppression des clients de test...');

        // Identifier les clients créés par les factories (critères heuristiques)
        $testClients = Client::where(function ($query) {
            $query->where('email', 'like', '%@example.%')
                  ->orWhere('email', 'like', '%@test.%')
                  ->orWhere('nom', 'like', '%Test%')
                  ->orWhere('nom', 'like', '%Factory%')
                  ->orWhere('notes', 'like', '%Client fidèle depuis%') // Pattern des factories
                  ->orWhere('notes', 'like', '%Client professionnel%'); // Pattern des factories
        })->get();

        $count = $testClients->count();

        if ($count === 0) {
            $this->info('Aucun client de test à supprimer.');
            return 0;
        }

        foreach ($testClients as $client) {
            $client->delete();
        }

        $this->info("✅ {$count} clients de test supprimés");
        return $count;
    }

    /**
     * Purge les utilisateurs de test
     */
    private function purgeTestUsers(): int
    {
        $this->info('👤 Suppression des utilisateurs de test...');

        // Identifier les utilisateurs de test
        $testUsers = User::where(function ($query) {
            $query->where('email', 'like', '%@test.%')
                  ->orWhere('email', 'like', '%@example.%')
                  ->orWhere('name', 'like', '%Test%')
                  ->orWhere('email', 'test@lesot.com'); // Utilisateur créé par le seeder
        })->get();

        $count = $testUsers->count();

        if ($count === 0) {
            $this->info('Aucun utilisateur de test à supprimer.');
            return 0;
        }

        foreach ($testUsers as $user) {
            $user->delete();
        }

        $this->info("✅ {$count} utilisateurs de test supprimés");
        return $count;
    }

    /**
     * Nettoie les fichiers orphelins
     */
    private function cleanupOrphanedFiles(): int
    {
        $this->info('🗂️ Nettoyage des fichiers orphelins...');

        $deletedFiles = 0;

        // Nettoyer les signatures orphelines
        $signatureDirectories = Storage::disk('public')->directories('signatures');
        foreach ($signatureDirectories as $dir) {
            $files = Storage::disk('public')->files($dir);
            foreach ($files as $file) {
                $basename = basename($file);
                
                // Vérifier si le fichier est référencé dans la base
                $exists = Attachement::where('signature_entreprise_path', $file)
                                   ->orWhere('signature_client_path', $file)
                                   ->exists();
                
                if (!$exists) {
                    Storage::disk('public')->delete($file);
                    $deletedFiles++;
                }
            }
        }

        // Nettoyer les PDFs orphelins
        $pdfDirectories = Storage::disk('public')->directories('pdf');
        foreach ($pdfDirectories as $dir) {
            $files = Storage::disk('public')->files($dir);
            foreach ($files as $file) {
                $exists = Attachement::where('pdf_path', $file)->exists();
                
                if (!$exists) {
                    Storage::disk('public')->delete($file);
                    $deletedFiles++;
                }
            }
        }

        if ($deletedFiles > 0) {
            $this->info("✅ {$deletedFiles} fichiers orphelins supprimés");
        } else {
            $this->info('Aucun fichier orphelin trouvé.');
        }

        return $deletedFiles;
    }

    /**
     * Affiche les résultats de la purge
     */
    private function displayResults(array $deletedCounts): void
    {
        $this->newLine();
        $this->info('📊 Résultats de la purge :');

        $tableData = [];
        foreach ($deletedCounts as $type => $count) {
            $tableData[] = [ucfirst($type), $count];
        }

        $this->table(['Type', 'Supprimés'], $tableData);

        $total = array_sum($deletedCounts);
        $this->info("🎉 Purge terminée ! Total : {$total} éléments supprimés");
    }
}