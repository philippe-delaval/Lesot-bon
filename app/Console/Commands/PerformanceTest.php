<?php

namespace App\Console\Commands;

use App\Models\Attachement;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\AttachementSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PerformanceTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:performance 
                           {count=10000 : Nombre d\'attachements à générer}
                           {--reset : Supprimer les données existantes avant le test}
                           {--memory : Afficher l\'utilisation mémoire}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test de performance avec génération de masse d\'attachements';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = (int) $this->argument('count');
        
        $this->info("🚀 Test de performance avec {$count} attachements");

        // Reset si demandé
        if ($this->option('reset')) {
            $this->resetData();
        }

        // Vérifications préliminaires
        $this->checkPrerequisites();

        // Mesures de performance
        $memoryStart = memory_get_usage(true);
        $timeStart = microtime(true);

        // Test de génération
        $this->runGenerationTest($count);

        // Mesures finales
        $timeEnd = microtime(true);
        $memoryEnd = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);

        // Calculs de performance
        $duration = $timeEnd - $timeStart;
        $memoryUsed = $memoryEnd - $memoryStart;
        $recordsPerSecond = $count / $duration;

        // Test de lecture
        $this->runReadTest();

        // Affichage des résultats
        $this->displayResults($count, $duration, $memoryUsed, $memoryPeak, $recordsPerSecond);

        return self::SUCCESS;
    }

    /**
     * Reset des données existantes
     */
    private function resetData(): void
    {
        $this->info('🧹 Suppression des données existantes...');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Attachement::truncate();
        Client::truncate();
        User::where('email', 'like', '%@test.%')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->info('✅ Données supprimées');
    }

    /**
     * Vérifications préliminaires
     */
    private function checkPrerequisites(): void
    {
        $this->info('🔍 Vérifications préliminaires...');

        // Vérifier l'espace disque disponible
        $freeSpace = disk_free_space(storage_path());
        $requiredSpace = 1024 * 1024 * 100; // 100 MB minimum

        if ($freeSpace < $requiredSpace) {
            $this->error('❌ Espace disque insuffisant');
            exit(1);
        }

        // Créer un utilisateur de test si nécessaire
        if (User::count() === 0) {
            User::factory()->create([
                'name' => 'Performance Test User',
                'email' => 'performance@test.com',
            ]);
        }

        $this->info('✅ Prérequis validés');
    }

    /**
     * Exécute le test de génération
     */
    private function runGenerationTest(int $count): void
    {
        $this->info("📊 Génération de {$count} attachements...");

        $seeder = new AttachementSeeder();
        
        // Appeler la méthode performance du seeder
        $seeder->setCommand($this);
        $seeder->performance($count);

        $this->info('✅ Génération terminée');
    }

    /**
     * Exécute le test de lecture
     */
    private function runReadTest(): void
    {
        $this->info('📖 Test de lecture des données...');

        $readStart = microtime(true);

        // Test de requêtes diverses
        $tests = [
            'Count total' => fn() => Attachement::count(),
            'Attachements récents' => fn() => Attachement::where('created_at', '>=', now()->subDays(7))->count(),
            'Avec relations' => fn() => Attachement::with(['client', 'creator'])->limit(100)->get()->count(),
            'Recherche' => fn() => Attachement::search('ATT')->limit(50)->get()->count(),
            'Pagination' => fn() => Attachement::paginate(15)->total(),
        ];

        $testResults = [];
        foreach ($tests as $testName => $testFn) {
            $testStart = microtime(true);
            $result = $testFn();
            $testDuration = microtime(true) - $testStart;
            
            $testResults[$testName] = [
                'result' => $result,
                'duration' => $testDuration * 1000, // en ms
            ];
        }

        $readDuration = microtime(true) - $readStart;

        $this->info("✅ Tests de lecture terminés en " . round($readDuration * 1000, 2) . " ms");

        // Affichage des résultats des tests
        $tableData = [];
        foreach ($testResults as $testName => $data) {
            $tableData[] = [
                $testName,
                $data['result'],
                round($data['duration'], 2) . ' ms'
            ];
        }

        $this->table(['Test', 'Résultat', 'Durée'], $tableData);
    }

    /**
     * Affiche les résultats de performance
     */
    private function displayResults(
        int $count,
        float $duration,
        int $memoryUsed,
        int $memoryPeak,
        float $recordsPerSecond
    ): void {
        $this->info('📊 Résultats de performance :');

        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Enregistrements générés', number_format($count)],
                ['Durée totale', round($duration, 2) . ' secondes'],
                ['Enregistrements/seconde', round($recordsPerSecond, 2)],
                ['Mémoire utilisée', $this->formatBytes($memoryUsed)],
                ['Pic mémoire', $this->formatBytes($memoryPeak)],
                ['Taille base de données', $this->getDatabaseSize()],
            ]
        );

        // Évaluation des performances
        $this->evaluatePerformance($recordsPerSecond, $memoryPeak);
    }

    /**
     * Évalue les performances
     */
    private function evaluatePerformance(float $recordsPerSecond, int $memoryPeak): void
    {
        $this->info('🎯 Évaluation des performances :');

        // Critères de performance
        $criteria = [
            'Vitesse de génération' => [
                'value' => $recordsPerSecond,
                'excellent' => 1000,
                'good' => 500,
                'unit' => 'records/sec'
            ],
            'Utilisation mémoire' => [
                'value' => $memoryPeak / (1024 * 1024), // en MB
                'excellent' => 256,
                'good' => 512,
                'unit' => 'MB',
                'lower_is_better' => true
            ]
        ];

        foreach ($criteria as $criteriaName => $data) {
            $value = $data['value'];
            $isLowerBetter = $data['lower_is_better'] ?? false;
            
            if ($isLowerBetter) {
                if ($value <= $data['excellent']) {
                    $status = '🟢 Excellent';
                } elseif ($value <= $data['good']) {
                    $status = '🟡 Bon';
                } else {
                    $status = '🔴 À améliorer';
                }
            } else {
                if ($value >= $data['excellent']) {
                    $status = '🟢 Excellent';
                } elseif ($value >= $data['good']) {
                    $status = '🟡 Bon';
                } else {
                    $status = '🔴 À améliorer';
                }
            }

            $this->line(sprintf(
                '  %s: %.2f %s - %s',
                $criteriaName,
                $value,
                $data['unit'],
                $status
            ));
        }
    }

    /**
     * Formate les bytes en unités lisibles
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Obtient la taille de la base de données
     */
    private function getDatabaseSize(): string
    {
        try {
            $driver = config('database.default');
            
            if ($driver === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                if (file_exists($dbPath)) {
                    return $this->formatBytes(filesize($dbPath));
                }
            } elseif ($driver === 'mysql') {
                $dbName = config('database.connections.mysql.database');
                $result = DB::select("
                    SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                    FROM information_schema.tables 
                    WHERE table_schema = ?
                ", [$dbName]);
                
                if ($result) {
                    return $result[0]->size_mb . ' MB';
                }
            }
            
            return 'N/A';
        } catch (\Exception $e) {
            return 'Erreur: ' . $e->getMessage();
        }
    }
}