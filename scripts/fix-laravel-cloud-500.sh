#!/bin/bash

# Script de correction d'erreur 500 Laravel Cloud
# Usage: bash fix-laravel-cloud-500.sh

set -e

echo "🔧 Script de correction erreur 500 - Laravel Cloud"
echo "=================================================="

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

print_step() {
    echo
    echo -e "${BLUE}=== $1 ===${NC}"
}

# ÉTAPE 1: Vérification de l'environnement
print_step "ÉTAPE 1: Vérification de l'environnement"

print_status "Vérification du répertoire courant..."
pwd
ls -la

print_status "Vérification des fichiers Laravel..."
if [ -f "artisan" ]; then
    print_success "Fichier artisan trouvé"
else
    print_error "Fichier artisan non trouvé - vérifiez le répertoire"
    exit 1
fi

# ÉTAPE 2: Vérification de la configuration .env
print_step "ÉTAPE 2: Vérification de la configuration .env"

if [ -f ".env" ]; then
    print_success "Fichier .env trouvé"
    print_status "Configuration actuelle:"
    echo "APP_ENV=$(grep '^APP_ENV=' .env | cut -d'=' -f2)"
    echo "APP_DEBUG=$(grep '^APP_DEBUG=' .env | cut -d'=' -f2)"
    echo "DB_CONNECTION=$(grep '^DB_CONNECTION=' .env | cut -d'=' -f2)"
    echo "DB_HOST=$(grep '^DB_HOST=' .env | cut -d'=' -f2)"
    echo "DB_DATABASE=$(grep '^DB_DATABASE=' .env | cut -d'=' -f2)"
else
    print_error "Fichier .env non trouvé"
    if [ -f ".env.example" ]; then
        print_status "Copie de .env.example vers .env..."
        cp .env.example .env
        print_warning "Configurez maintenant votre .env avec les bonnes valeurs"
    fi
    exit 1
fi

# ÉTAPE 3: Activation temporaire du debug
print_step "ÉTAPE 3: Activation temporaire du debug"

print_status "Activation du mode debug pour diagnostic..."
sed -i 's/APP_DEBUG=false/APP_DEBUG=true/g' .env
print_success "Mode debug activé"

# ÉTAPE 4: Nettoyage des caches
print_step "ÉTAPE 4: Nettoyage des caches Laravel"

print_status "Nettoyage du cache de configuration..."
php artisan config:clear || print_warning "Échec config:clear"

print_status "Nettoyage du cache des routes..."
php artisan route:clear || print_warning "Échec route:clear"

print_status "Nettoyage du cache des vues..."
php artisan view:clear || print_warning "Échec view:clear"

print_status "Nettoyage optimisation générale..."
php artisan optimize:clear || print_warning "Échec optimize:clear"

print_success "Caches nettoyés"

# ÉTAPE 5: Test de connexion base de données
print_step "ÉTAPE 5: Test de connexion base de données"

print_status "Test de connexion PostgreSQL..."
php artisan tinker --execute="
try {
    \$pdo = DB::connection()->getPdo();
    echo 'Connexion DB: SUCCESS' . PHP_EOL;
    echo 'Driver: ' . \$pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . PHP_EOL;
    echo 'Version: ' . \$pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
} catch (Exception \$e) {
    echo 'Connexion DB: FAILED - ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

if [ $? -eq 0 ]; then
    print_success "Connexion base de données OK"
else
    print_error "Échec de connexion à la base de données"
    print_status "Vérifiez vos paramètres DB dans .env"
    exit 1
fi

# ÉTAPE 6: Vérification et application des migrations
print_step "ÉTAPE 6: Migrations de base de données"

print_status "Vérification du statut des migrations..."
php artisan migrate:status || print_warning "Impossible de vérifier le statut des migrations"

print_status "Application des migrations manquantes..."
php artisan migrate --force

if [ $? -eq 0 ]; then
    print_success "Migrations appliquées avec succès"
else
    print_warning "Problème avec les migrations - continuons..."
fi

# ÉTAPE 7: Vérification des permissions
print_step "ÉTAPE 7: Vérification et correction des permissions"

print_status "Correction des permissions storage..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
print_success "Permissions corrigées"

print_status "Vérification des répertoires critiques..."
ls -la storage/
ls -la bootstrap/cache/

# ÉTAPE 8: Réinstallation des dépendances
print_step "ÉTAPE 8: Réinstallation des dépendances"

print_status "Réinstallation des dépendances Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

if [ $? -eq 0 ]; then
    print_success "Dépendances Composer réinstallées"
else
    print_warning "Problème avec Composer - continuons..."
fi

# ÉTAPE 9: Génération de la clé d'application
print_step "ÉTAPE 9: Génération de la clé d'application"

if grep -q "^APP_KEY=$" .env; then
    print_status "Génération de la clé d'application..."
    php artisan key:generate --force
    print_success "Clé d'application générée"
else
    print_status "Clé d'application déjà présente"
fi

# ÉTAPE 10: Optimisation pour production
print_step "ÉTAPE 10: Optimisation pour production"

print_status "Cache de configuration..."
php artisan config:cache

print_status "Cache des routes..."
php artisan route:cache

print_status "Cache des vues..."
php artisan view:cache

print_success "Optimisations appliquées"

# ÉTAPE 11: Test de l'application
print_step "ÉTAPE 11: Tests de validation"

print_status "Test des modèles principaux..."
php artisan tinker --execute="
try {
    echo 'Modèles disponibles:' . PHP_EOL;
    echo 'Users: ' . App\Models\User::count() . PHP_EOL;
    echo 'Application fonctionne correctement' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Erreur modèles: ' . \$e->getMessage() . PHP_EOL;
}
"

# ÉTAPE 12: Vérification des logs
print_step "ÉTAPE 12: Vérification des logs"

print_status "Dernières erreurs Laravel (si présentes)..."
if [ -f "storage/logs/laravel.log" ]; then
    echo "--- DERNIÈRES ERREURS LARAVEL ---"
    tail -20 storage/logs/laravel.log | grep -E "(ERROR|CRITICAL|emergency)" || echo "Aucune erreur récente trouvée"
else
    print_status "Aucun fichier de log Laravel trouvé"
fi

# ÉTAPE 13: Désactivation du debug (optionnel)
print_step "ÉTAPE 13: Sécurisation (optionnel)"

echo
print_warning "IMPORTANT: Mode debug actuellement activé!"
echo "Pour désactiver le debug en production, exécutez:"
echo "sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' .env"
echo "php artisan config:cache"

# ÉTAPE 14: Résumé et prochaines étapes
print_step "ÉTAPE 14: Résumé"

echo
print_success "🎉 Script de correction terminé!"
echo
print_status "Prochaines étapes:"
echo "1. Testez votre application dans le navigateur"
echo "2. Si l'erreur persiste, vérifiez les logs en temps réel:"
echo "   tail -f storage/logs/laravel.log"
echo "3. Désactivez le mode debug une fois le problème résolu"
echo "4. Si problème persistant, vérifiez les logs du serveur web"

echo
print_status "Informations de debug utiles:"
echo "- URL de l'application: \$(grep '^APP_URL=' .env | cut -d'=' -f2)"
echo "- Environnement: \$(grep '^APP_ENV=' .env | cut -d'=' -f2)"
echo "- Debug activé: \$(grep '^APP_DEBUG=' .env | cut -d'=' -f2)"
echo "- Base de données: \$(grep '^DB_CONNECTION=' .env | cut -d'=' -f2)"

echo
print_success "Script terminé avec succès ✅"