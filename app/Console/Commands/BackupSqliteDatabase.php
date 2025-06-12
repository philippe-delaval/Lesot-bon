<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupSqliteDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup-sqlite 
                           {--format=both : Format de backup (file|dump|both)} 
                           {--compress : Compresser le backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer une sauvegarde de la base de données SQLite';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Vérifier que nous utilisons SQLite
        if (config('database.default') !== 'sqlite') {
            $this->error('Cette commande ne fonctionne que avec SQLite');
            return 1;
        }

        $dbPath = database_path('database.sqlite');
        
        // Vérifier que le fichier de base existe
        if (!File::exists($dbPath)) {
            $this->error('Fichier de base de données SQLite introuvable : ' . $dbPath);
            return 1;
        }

        // Créer le répertoire de backup s'il n'existe pas
        $backupDir = storage_path('backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $format = $this->option('format');
        $compress = $this->option('compress');

        $this->info('Début de la sauvegarde SQLite...');

        $backups = [];

        // Sauvegarde du fichier de base
        if ($format === 'file' || $format === 'both') {
            $backupFile = $backupDir . "/database_backup_{$timestamp}.sqlite";
            
            if (File::copy($dbPath, $backupFile)) {
                $backups[] = $backupFile;
                $this->info('✓ Sauvegarde fichier créée : ' . basename($backupFile));
                
                // Compression si demandée
                if ($compress) {
                    $compressedFile = $backupFile . '.gz';
                    if ($this->compressFile($backupFile, $compressedFile)) {
                        File::delete($backupFile);
                        $backups[count($backups) - 1] = $compressedFile;
                        $this->info('✓ Fichier compressé : ' . basename($compressedFile));
                    }
                }
            } else {
                $this->error('✗ Échec de la copie du fichier de base');
            }
        }

        // Dump SQL
        if ($format === 'dump' || $format === 'both') {
            $dumpFile = $backupDir . "/database_dump_{$timestamp}.sql";
            
            if ($this->createSqlDump($dbPath, $dumpFile)) {
                $backups[] = $dumpFile;
                $this->info('✓ Dump SQL créé : ' . basename($dumpFile));
                
                // Compression si demandée
                if ($compress) {
                    $compressedFile = $dumpFile . '.gz';
                    if ($this->compressFile($dumpFile, $compressedFile)) {
                        File::delete($dumpFile);
                        $backups[count($backups) - 1] = $compressedFile;
                        $this->info('✓ Dump compressé : ' . basename($compressedFile));
                    }
                }
            } else {
                $this->error('✗ Échec de la création du dump SQL');
            }
        }

        // Afficher les informations de sauvegarde
        if (!empty($backups)) {
            $this->newLine();
            $this->info('Sauvegardes créées avec succès :');
            
            foreach ($backups as $backup) {
                $size = $this->formatBytes(File::size($backup));
                $this->line('  • ' . basename($backup) . ' (' . $size . ')');
            }

            // Nettoyage des anciens backups (garder les 10 plus récents)
            $this->cleanOldBackups($backupDir);
        }

        return 0;
    }

    /**
     * Créer un dump SQL de la base SQLite
     */
    private function createSqlDump(string $dbPath, string $dumpFile): bool
    {
        try {
            // Utiliser sqlite3 si disponible
            if ($this->commandExists('sqlite3')) {
                $command = sprintf(
                    'sqlite3 %s .dump > %s',
                    escapeshellarg($dbPath),
                    escapeshellarg($dumpFile)
                );
                
                exec($command, $output, $returnCode);
                return $returnCode === 0;
            }

            // Fallback avec PDO si sqlite3 n'est pas disponible
            return $this->createSqlDumpWithPdo($dbPath, $dumpFile);

        } catch (\Exception $e) {
            $this->error('Erreur lors de la création du dump : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Créer un dump SQL avec PDO
     */
    private function createSqlDumpWithPdo(string $dbPath, string $dumpFile): bool
    {
        try {
            $pdo = new \PDO("sqlite:$dbPath");
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $dump = "-- SQLite Dump\n";
            $dump .= "-- Generated: " . now()->toDateTimeString() . "\n\n";
            $dump .= "PRAGMA foreign_keys=OFF;\n";
            $dump .= "BEGIN TRANSACTION;\n\n";

            // Obtenir la liste des tables
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            
            foreach ($tables as $table) {
                $tableName = $table['name'];
                
                // Schema de la table
                $schema = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$tableName'")->fetchColumn();
                $dump .= "$schema;\n\n";
                
                // Données de la table
                $rows = $pdo->query("SELECT * FROM `$tableName`");
                foreach ($rows as $row) {
                    $values = array_map(function($value) {
                        return $value === null ? 'NULL' : "'" . str_replace("'", "''", $value) . "'";
                    }, array_values($row));
                    
                    $dump .= "INSERT INTO `$tableName` VALUES(" . implode(',', $values) . ");\n";
                }
                $dump .= "\n";
            }

            $dump .= "COMMIT;\n";
            $dump .= "PRAGMA foreign_keys=ON;\n";
            
            return File::put($dumpFile, $dump) !== false;

        } catch (\Exception $e) {
            $this->error('Erreur PDO : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Compresser un fichier avec gzip
     */
    private function compressFile(string $sourceFile, string $targetFile): bool
    {
        try {
            $data = File::get($sourceFile);
            $compressed = gzencode($data, 9);
            return File::put($targetFile, $compressed) !== false;
        } catch (\Exception $e) {
            $this->error('Erreur de compression : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Nettoyer les anciens backups
     */
    private function cleanOldBackups(string $backupDir): void
    {
        $files = File::glob($backupDir . '/database_*');
        
        if (count($files) > 10) {
            // Trier par date de modification
            usort($files, function($a, $b) {
                return File::lastModified($b) - File::lastModified($a);
            });
            
            // Supprimer les plus anciens
            $toDelete = array_slice($files, 10);
            foreach ($toDelete as $file) {
                File::delete($file);
                $this->line('  • Ancien backup supprimé : ' . basename($file));
            }
        }
    }

    /**
     * Vérifier si une commande existe
     */
    private function commandExists(string $command): bool
    {
        $which = shell_exec("which $command");
        return !empty($which);
    }

    /**
     * Formater la taille en bytes
     */
    private function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }
}