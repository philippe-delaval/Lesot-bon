#!/bin/bash

# Script de déploiement PostgreSQL pour Laravel
# Usage: ./scripts/deploy-postgresql.sh

set -e

echo "🚀 Déploiement PostgreSQL - Lesot Attachements"
echo "================================================"

# Configuration
BACKUP_DIR="storage/backups"
MIGRATION_TIMEOUT=300

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Vérifications préliminaires
check_prerequisites() {
    print_status "Vérification des prérequis..."
    
    # Vérifier que nous sommes dans le bon répertoire
    if [ ! -f "artisan" ]; then
        print_error "Ce script doit être exécuté depuis la racine du projet Laravel"
        exit 1
    fi
    
    # Vérifier la présence de .env.production
    if [ ! -f ".env.production" ]; then
        print_error "Fichier .env.production manquant"
        print_status "Copiez .env.production.example vers .env.production et configurez-le"
        exit 1
    fi
    
    # Vérifier la connexion PostgreSQL
    print_status "Test de connexion PostgreSQL..."
    APP_ENV=production php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        print_success "Connexion PostgreSQL OK"
    else
        print_error "Impossible de se connecter à PostgreSQL"
        print_status "Vérifiez vos paramètres de connexion dans .env.production"
        exit 1
    fi
    
    print_success "Prérequis validés"
}

# Sauvegarde SQLite avant migration
backup_sqlite() {
    if [ -f "database/database.sqlite" ] && [ "$DB_CONNECTION" != "pgsql" ]; then
        print_status "Sauvegarde de la base SQLite..."
        
        # Créer le répertoire de backup
        mkdir -p "$BACKUP_DIR"
        
        # Backup avec la commande Artisan
        php artisan db:backup-sqlite --format=both --compress
        
        if [ $? -eq 0 ]; then
            print_success "Sauvegarde SQLite terminée"
        else
            print_warning "Échec de la sauvegarde SQLite (non critique)"
        fi
    fi
}

# Migration vers PostgreSQL
migrate_to_postgresql() {
    print_status "Configuration de l'environnement de production..."
    
    # Sauvegarder .env actuel
    if [ -f ".env" ]; then
        cp .env .env.backup
        print_status "Sauvegarde de .env vers .env.backup"
    fi
    
    # Utiliser la configuration de production
    cp .env.production .env
    
    print_status "Nettoyage du cache Laravel..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    print_status "Exécution des migrations PostgreSQL..."
    
    # Timeout pour les migrations
    timeout $MIGRATION_TIMEOUT php artisan migrate --force
    if [ $? -eq 0 ]; then
        print_success "Migrations PostgreSQL terminées"
    else
        print_error "Échec des migrations PostgreSQL"
        print_status "Restauration de la configuration précédente..."
        if [ -f ".env.backup" ]; then
            mv .env.backup .env
        fi
        exit 1
    fi
}

# Optimisation pour la production
optimize_production() {
    print_status "Optimisation pour la production..."
    
    # Optimisations Laravel
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Build des assets (si Vite)
    if [ -f "package.json" ]; then
        print_status "Build des assets frontend..."
        npm ci --only=production
        npm run build
    fi
    
    print_success "Optimisations terminées"
}

# Tests de validation
run_validation_tests() {
    print_status "Tests de validation..."
    
    # Test de la connexion à la base
    php artisan tinker --execute="
        echo 'Connexion: ' . config('database.default') . PHP_EOL;
        echo 'Tables: ' . implode(', ', DB::connection()->getSchemaBuilder()->getTableListing()) . PHP_EOL;
        echo 'Utilisateurs: ' . App\Models\User::count() . PHP_EOL;
        echo 'Clients: ' . App\Models\Client::count() . PHP_EOL;
        echo 'Attachements: ' . App\Models\Attachement::count() . PHP_EOL;
    "
    
    if [ $? -eq 0 ]; then
        print_success "Tests de validation réussis"
    else
        print_warning "Certains tests de validation ont échoué"
    fi
}

# Nettoyage post-déploiement
cleanup() {
    print_status "Nettoyage post-déploiement..."
    
    # Supprimer les fichiers temporaires
    rm -f .env.backup
    
    # Nettoyer les anciens logs
    find storage/logs -name "*.log" -mtime +7 -delete 2>/dev/null || true
    
    print_success "Nettoyage terminé"
}

# Fonction principale
main() {
    echo
    print_status "Début du déploiement PostgreSQL..."
    echo
    
    # Étapes du déploiement
    check_prerequisites
    backup_sqlite
    migrate_to_postgresql
    optimize_production
    run_validation_tests
    cleanup
    
    echo
    print_success "🎉 Déploiement PostgreSQL terminé avec succès!"
    echo
    print_status "Prochaines étapes:"
    echo "  1. Vérifiez que l'application fonctionne correctement"
    echo "  2. Configurez les sauvegardes automatiques PostgreSQL"
    echo "  3. Mettez à jour vos scripts de déploiement CI/CD"
    echo
}

# Gestion des signaux d'interruption
trap cleanup EXIT

# Exécution
main "$@"