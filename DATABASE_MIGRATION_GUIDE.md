# Guide de Migration SQLite vers PostgreSQL

## Vue d'ensemble

Ce guide décrit la configuration multi-environnement permettant d'utiliser SQLite en développement local et PostgreSQL en production.

## Configuration

### Environnements

**Local (Développement)**
- Base de données : SQLite (`database/database.sqlite`)
- Configuration automatique si `APP_ENV != production`

**Production**
- Base de données : PostgreSQL (Laravel Cloud)
- Configuration automatique si `APP_ENV = production`

### Fichiers de configuration

**`.env.production.example`**
```bash
# Configuration de production PostgreSQL
APP_ENV=production
DB_CONNECTION=pgsql
DB_HOST=ep-polished-truth-a2zhcpx1.aws-eu-central-1.pg.laravel.cloud
DB_PORT=5432
DB_DATABASE=main
DB_USERNAME=laravel
DB_PASSWORD=npg_BXeQT4uCYZ7v
DB_SSLMODE=require
```

## Commandes disponibles

### 1. Sauvegarde SQLite

```bash
# Sauvegarde basique (fichier + dump SQL)
php artisan db:backup-sqlite

# Sauvegarde avec compression
php artisan db:backup-sqlite --compress

# Seulement le fichier SQLite
php artisan db:backup-sqlite --format=file

# Seulement le dump SQL
php artisan db:backup-sqlite --format=dump
```

**Caractéristiques :**
- Sauvegarde dans `storage/backups/`
- Nettoyage automatique (garde les 10 plus récents)
- Support gzip pour compression
- Fallback PDO si sqlite3 non disponible

### 2. Migration de données

```bash
# Simulation (aucune modification)
php artisan db:migrate-sqlite-to-pgsql --dry-run

# Migration complète
php artisan db:migrate-sqlite-to-pgsql

# Migration avec taille de lot personnalisée
php artisan db:migrate-sqlite-to-pgsql --chunk-size=50
```

**Processus :**
1. Vérification des connexions
2. Sauvegarde automatique SQLite
3. Migration par chunks (users → clients → attachements)
4. Mise à jour des séquences PostgreSQL
5. Validation finale

### 3. Déploiement production

```bash
# Déploiement automatisé
./scripts/deploy-postgresql.sh
```

**Étapes automatiques :**
1. Vérifications préliminaires
2. Sauvegarde SQLite
3. Configuration production
4. Migrations PostgreSQL
5. Optimisations Laravel
6. Tests de validation

## Compatibilité des schémas

### Modifications appliquées

**Types de données :**
- `$table->id()` → `$table->bigIncrements('id')`
- `$table->timestamps()` → `$table->timestampsTz(precision: 6)`
- `$table->foreignId()` → `$table->unsignedBigInteger()` + `$table->foreign()`

**Contraintes :**
- Clés étrangères explicites
- `onDelete('set null')` pour relations optionnelles
- `onDelete('restrict')` pour relations obligatoires

**Index :**
- Index composites pour recherches
- Index sur clés étrangères
- Limitation chaînes à 191 caractères

## Optimisations

### PostgreSQL (AppServiceProvider)

```php
if (config('database.default') === 'pgsql') {
    Schema::defaultStringLength(191);
    
    $platform = DB::getDoctrineSchemaManager()->getDatabasePlatform();
    $platform->registerDoctrineTypeMapping('enum', 'string');
    $platform->registerDoctrineTypeMapping('json', 'text');
    $platform->registerDoctrineTypeMapping('jsonb', 'text');
}
```

### SQLite (AppServiceProvider)

```php
if (config('database.default') === 'sqlite') {
    DB::statement('PRAGMA foreign_keys=ON');
}
```

## Commandes de maintenance

### Vérification de l'état

```bash
# Connexion et tables
php artisan tinker --execute="
    echo 'DB: ' . config('database.default');
    echo 'Tables: ' . implode(', ', Schema::getTableListing());
"

# Statistiques
php artisan tinker --execute="
    echo 'Users: ' . App\Models\User::count();
    echo 'Clients: ' . App\Models\Client::count();
    echo 'Attachements: ' . App\Models\Attachement::count();
"
```

### Nettoyage

```bash
# Cache Laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimisations production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Dépannage

### Problèmes courants

**Erreur de connexion PostgreSQL :**
```bash
# Vérifier les paramètres
php artisan tinker --execute="DB::connection('pgsql')->getPdo();"
```

**Séquences PostgreSQL désynchronisées :**
```bash
# Remettre à jour les séquences
php artisan tinker --execute="
    \$maxId = DB::table('users')->max('id') ?? 0;
    DB::statement('SELECT setval(\'users_id_seq\', \$maxId)');
"
```

**Contraintes de clés étrangères :**
```bash
# Vérifier les contraintes
php artisan tinker --execute="
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    // ... opérations
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
"
```

### Logs utiles

**Laravel :**
- `storage/logs/laravel.log`

**Migrations :**
```bash
php artisan migrate:status
php artisan migrate:rollback --step=1
```

## Sécurité

### Variables sensibles

**Ne jamais commiter :**
- `.env.production`
- Mots de passe de base de données
- Clés d'API

**Recommandations :**
- Utiliser des variables d'environnement
- Rotation régulière des mots de passe
- Connexions SSL/TLS obligatoires
- Sauvegardes chiffrées

### Permissions

```bash
# Fichiers de configuration
chmod 600 .env.production

# Scripts de déploiement
chmod +x scripts/deploy-postgresql.sh

# Répertoire de sauvegardes
chmod 755 storage/backups/
```

## Performance

### Index recommandés

```sql
-- PostgreSQL
CREATE INDEX CONCURRENTLY idx_attachements_client_id ON attachements(client_id);
CREATE INDEX CONCURRENTLY idx_attachements_date ON attachements(date_intervention);
CREATE INDEX CONCURRENTLY idx_clients_search ON clients(nom, email);
```

### Monitoring

```bash
# Taille des tables
php artisan tinker --execute="
    DB::select('SELECT schemaname, tablename, pg_size_pretty(pg_total_relation_size(schemaname||\'.\' ||tablename)) as size FROM pg_tables WHERE schemaname = \'public\' ORDER BY pg_total_relation_size(schemaname||\'.\'||tablename) DESC;')
"

# Connexions actives
php artisan tinker --execute="
    DB::select('SELECT count(*) as active_connections FROM pg_stat_activity WHERE state = \'active\';')
"
```