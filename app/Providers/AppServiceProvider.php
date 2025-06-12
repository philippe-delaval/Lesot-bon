<?php

namespace App\Providers;

use App\Models\Attachement;
use App\Observers\AttachementObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrement des observateurs
        Attachement::observe(AttachementObserver::class);

        // Configuration pour PostgreSQL
        if (config('database.default') === 'pgsql') {
            // Limite la longueur des chaînes pour les index
            Schema::defaultStringLength(191);
            
            // Mapping des types Doctrine pour PostgreSQL
            try {
                $platform = DB::getDoctrineSchemaManager()->getDatabasePlatform();
                $platform->registerDoctrineTypeMapping('enum', 'string');
                $platform->registerDoctrineTypeMapping('json', 'text');
                $platform->registerDoctrineTypeMapping('jsonb', 'text');
            } catch (\Exception $e) {
                // Ignore les erreurs si Doctrine n'est pas disponible
            }
        }

        // Configuration pour SQLite
        if (config('database.default') === 'sqlite') {
            // Active les contraintes de clés étrangères pour SQLite
            DB::statement('PRAGMA foreign_keys=ON');
        }
    }
}
