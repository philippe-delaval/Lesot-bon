# Commandes SSH Laravel Cloud - Correction Erreur 500

## 🚀 Méthode 1: Script automatique (RECOMMANDÉE)

```bash
# 1. Se connecter en SSH à Laravel Cloud
ssh forge@your-server-ip

# 2. Aller dans le répertoire de l'application
cd /home/forge/default

# 3. Télécharger et exécuter le script de correction
curl -o fix-500.sh https://raw.githubusercontent.com/your-username/lesot-bon/master/scripts/fix-laravel-cloud-500.sh
chmod +x fix-500.sh
bash fix-500.sh
```

## 🔧 Méthode 2: Commandes manuelles étape par étape

### Étape 1: Connexion et vérification
```bash
# Connexion SSH
ssh forge@your-server-ip

# Aller dans le répertoire de l'application
cd /home/forge/default

# Vérifier les fichiers
ls -la
pwd
```

### Étape 2: Activation du debug temporaire
```bash
# Activer le debug pour diagnostic
sed -i 's/APP_DEBUG=false/APP_DEBUG=true/g' .env

# Vérifier la configuration
cat .env | grep -E "(APP_|DB_)"
```

### Étape 3: Nettoyage des caches
```bash
# Nettoyage complet des caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Étape 4: Test de connexion base de données
```bash
# Test de connexion PostgreSQL
php artisan tinker --execute="
try {
    \$pdo = DB::connection()->getPdo();
    echo 'Connexion DB: SUCCESS' . PHP_EOL;
    echo 'Driver: ' . \$pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . PHP_EOL;
} catch (Exception \$e) {
    echo 'Connexion DB: FAILED - ' . \$e->getMessage() . PHP_EOL;
}
exit;
"
```

### Étape 5: Migrations
```bash
# Vérifier le statut des migrations
php artisan migrate:status

# Appliquer les migrations manquantes
php artisan migrate --force
```

### Étape 6: Permissions
```bash
# Corriger les permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Créer les répertoires manquants si nécessaire
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
```

### Étape 7: Réinstallation dépendances
```bash
# Réinstaller les dépendances Composer
composer install --no-dev --optimize-autoloader --no-interaction
```

### Étape 8: Clé d'application
```bash
# Générer la clé d'application si manquante
php artisan key:generate --force
```

### Étape 9: Optimisation production
```bash
# Cache de configuration
php artisan config:cache

# Cache des routes
php artisan route:cache

# Cache des vues
php artisan view:cache
```

### Étape 10: Test et validation
```bash
# Test des modèles
php artisan tinker --execute="
echo 'Users: ' . App\Models\User::count() . PHP_EOL;
echo 'Test OK' . PHP_EOL;
exit;
"

# Vérifier les logs récents
tail -20 storage/logs/laravel.log | grep ERROR
```

### Étape 11: Sécurisation (après résolution)
```bash
# Désactiver le debug en production
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' .env
php artisan config:cache
```

## 🔍 Commandes de diagnostic supplémentaires

### Vérification des logs en temps réel
```bash
# Surveiller les logs Laravel
tail -f storage/logs/laravel.log

# Surveiller les logs Nginx (si accessible)
sudo tail -f /var/log/nginx/error.log
```

### Vérification de la base de données
```bash
# Informations détaillées sur la DB
php artisan tinker --execute="
echo 'Database: ' . config('database.default') . PHP_EOL;
echo 'Host: ' . config('database.connections.pgsql.host') . PHP_EOL;
echo 'Database: ' . config('database.connections.pgsql.database') . PHP_EOL;
echo 'Tables: ' . implode(', ', Schema::getTableListing()) . PHP_EOL;
exit;
"
```

### Test des routes
```bash
# Lister toutes les routes
php artisan route:list

# Tester une route spécifique
php artisan tinker --execute="
\$response = app()->make('Illuminate\Contracts\Http\Kernel')->handle(
    \$request = Illuminate\Http\Request::create('/', 'GET')
);
echo 'Status: ' . \$response->getStatusCode() . PHP_EOL;
exit;
"
```

## 🚨 En cas de problème persistant

### Vérification des variables d'environnement
```bash
# Vérifier que toutes les variables sont définies
php artisan tinker --execute="
echo 'APP_KEY: ' . (config('app.key') ? 'SET' : 'NOT SET') . PHP_EOL;
echo 'DB_PASSWORD: ' . (config('database.connections.pgsql.password') ? 'SET' : 'NOT SET') . PHP_EOL;
exit;
"
```

### Reset complet (ATTENTION: perte de données)
```bash
# UNIQUEMENT en dernier recours
php artisan migrate:fresh --force
php artisan db:seed --force
```

### Vérification de la configuration du serveur web
```bash
# Si vous avez accès aux configs Nginx
sudo nginx -t
sudo systemctl reload nginx

# Vérifier PHP-FPM
sudo systemctl status php8.2-fpm
```

## 📋 Checklist de vérification

✅ Connexion SSH établie  
✅ Répertoire d'application correct  
✅ Fichier .env présent et configuré  
✅ Base de données accessible  
✅ Migrations appliquées  
✅ Permissions correctes  
✅ Caches nettoyés  
✅ Dépendances installées  
✅ Clé d'application générée  
✅ Optimisations appliquées  
✅ Logs vérifiés  
✅ Mode debug désactivé (production)  

## 🆘 Support

Si le problème persiste après ces étapes:

1. **Copiez les logs d'erreur complets**:
```bash
cat storage/logs/laravel.log | tail -50
```

2. **Vérifiez la configuration exacte**:
```bash
cat .env | grep -v PASSWORD
```

3. **Testez en local** avec les mêmes paramètres de production

4. **Contactez le support Laravel Cloud** avec les logs détaillés