# Guide de Génération d'Attachements Factices

## 🎯 Vue d'ensemble

Ce système permet la génération complète de données de test pour les bons d'attachements avec des données réalistes, des tests complets et des outils de performance.

## 📋 Composants Implémentés

### 1. Factory & Seeder
- ✅ **AttachementFactory** avec états multiples (recent, emergency, withManySupplies, withoutClientSignature)
- ✅ **ClientFactory** avec données régionales Nord-Pas-de-Calais
- ✅ **AttachementSeeder** optimisé avec barres de progression
- ✅ **AttachementObserver** pour l'historique automatique

### 2. Tests Complets
- ✅ **Tests de génération** (AttachementGenerationTest.php)
- ✅ **Tests d'affichage Inertia** (AttachementListTest.php)
- ✅ **Tests de couverture 100%** (AttachementCoverageTest.php)

### 3. Outils de Performance
- ✅ **Commande de test de performance** (10 000+ entrées)
- ✅ **Script de purge des données** de test
- ✅ **Interfaces TypeScript** complètes

## 🚀 Commandes d'Exécution

### Génération de Données de Test

```bash
# Génération standard (50 attachements variés)
php artisan db:seed --class=AttachementSeeder

# Génération de performance (10 000 attachements)
php artisan test:performance 10000

# Génération avec reset des données
php artisan test:performance 5000 --reset

# Génération avec monitoring mémoire
php artisan test:performance 1000 --memory
```

### Exécution des Tests

```bash
# Tests de génération de base
php artisan test tests/Feature/AttachementGenerationTest.php

# Tests d'affichage Inertia
php artisan test tests/Feature/AttachementListTest.php

# Tests de couverture complète
php artisan test tests/Feature/AttachementCoverageTest.php

# Tous les tests d'attachements
php artisan test --filter=Attachement

# Tests avec couverture de code
php artisan test --coverage
```

### Gestion des Données

```bash
# Purge des données de test (avec confirmation)
php artisan data:purge-test

# Purge forcée sans confirmation
php artisan data:purge-test --force

# Purge en conservant les utilisateurs
php artisan data:purge-test --keep-users

# Purge seulement les attachements
php artisan data:purge-test --only-attachements
```

## 📊 États de Factory Disponibles

### AttachementFactory

```php
// État par défaut - attachement complet
Attachement::factory()->create();

// Attachement récent (7 derniers jours)
Attachement::factory()->recent()->create();

// Attachement d'urgence (intervention rapide)
Attachement::factory()->emergency()->create();

// Attachement avec beaucoup de fournitures (8+ items)
Attachement::factory()->withManySupplies()->create();

// Attachement sans signature client
Attachement::factory()->withoutClientSignature()->create();

// Combinaison d'états
Attachement::factory()->recent()->emergency()->create();
```

### ClientFactory

```php
// Client par défaut (mixte particulier/entreprise)
Client::factory()->create();

// Client entreprise
Client::factory()->company()->create();

// Client particulier
Client::factory()->individual()->create();

// Client avec historique
Client::factory()->withHistory()->create();
```

## 🔧 Structure des Données Générées

### Attachement
```json
{
  "numero_dossier": "ATT-20250612-ABCD",
  "client_nom": "Dupont Jean",
  "date_intervention": "2025-06-12",
  "designation_travaux": "Travaux de plomberie urgents",
  "fournitures_travaux": [
    {
      "designation": "Plomberie - Réparation fuite",
      "quantite": "2 heures",
      "observations": "Intervention rapide requise"
    }
  ],
  "temps_total_passe": 2.5,
  "geolocation": {
    "latitude": 50.6292,
    "longitude": 3.0573,
    "timestamp": "2025-06-12T10:30:00+00:00"
  }
}
```

### Client (Nord-Pas-de-Calais)
```json
{
  "nom": "SARL Dupont & Fils",
  "email": "contact@dupont-fils.fr",
  "adresse": "123 rue de la Paix",
  "code_postal": "59000",
  "ville": "Lille",
  "notes": "Client professionnel - Entreprise familiale"
}
```

## 📈 Métriques de Performance

### Objectifs de Performance
- **Génération** : >500 attachements/seconde
- **Mémoire** : <512 MB pour 10 000 entrées
- **Tests** : <30 secondes pour suite complète
- **Lecture** : <100ms pour pagination (15 items)

### Surveillance

```bash
# Test de performance avec métriques détaillées
php artisan test:performance 1000 --memory

# Vérification de la base de données
php artisan test tests/Feature/AttachementCoverageTest.php::test_couverture_performance_et_limites
```

## 🎨 Types de Données Générées

### Variété des Interventions
- Plomberie (fuites, réparations)
- Électricité (installations, dépannages)
- Chauffage (maintenance, pannes)
- Menuiserie (fenêtres, portes)
- Carrelage et sols
- Serrurerie d'urgence

### Géolocalisation Réaliste
- Coordonnées Nord-Pas-de-Calais
- Villes : Lille, Arras, Calais, Lens, Béthune, etc.
- Codes postaux corrects (59xxx, 62xxx)

### Clients Diversifiés
- Particuliers (noms français)
- Entreprises (SARL, SAS, EURL)
- Copropriétés et syndics
- Mix urbain/rural

## 🛡️ Sécurité et Bonnes Pratiques

### Protection Production
```php
// Vérification automatique dans PurgeTestData
if (app()->environment('production')) {
    $this->error('❌ Cette commande ne peut pas être exécutée en production !');
    return self::FAILURE;
}
```

### Observateur d'Historique
```php
// Log automatique des actions
Log::info('Attachement créé', [
    'id' => $attachement->id,
    'numero_dossier' => $attachement->numero_dossier,
    'action' => 'created',
    'timestamp' => now()
]);
```

### Nettoyage Automatique
- Suppression des fichiers orphelins
- Gestion des contraintes de clés étrangères
- Optimisation des performances avec chunking

## 📝 Utilisation en Développement

### Workflow Recommandé

1. **Génération initiale**
   ```bash
   php artisan db:seed --class=AttachementSeeder
   ```

2. **Vérification des tests**
   ```bash
   php artisan test --filter=Attachement
   ```

3. **Test de performance**
   ```bash
   php artisan test:performance 1000
   ```

4. **Nettoyage périodique**
   ```bash
   php artisan data:purge-test --force
   ```

### Intégration Continue

```bash
# Dans vos scripts CI/CD
php artisan test tests/Feature/AttachementGenerationTest.php
php artisan test tests/Feature/AttachementListTest.php
php artisan test:performance 100 --reset
```

## 🚨 Dépannage

### Problèmes Courants

1. **Erreur de mémoire**
   ```bash
   # Augmenter la limite mémoire
   php -d memory_limit=1G artisan test:performance 10000
   ```

2. **Contraintes de clés étrangères**
   ```bash
   # Reset complet de la base
   php artisan migrate:fresh --seed
   ```

3. **Fichiers orphelins**
   ```bash
   # Nettoyage automatique
   php artisan data:purge-test --force
   ```

## 📞 Support

En cas de problème avec la génération de données de test :

1. Vérifier les logs : `storage/logs/laravel.log`
2. Exécuter les tests de couverture
3. Utiliser la commande de purge pour reset
4. Consulter les métriques de performance

---

**✅ Système complet de génération de données de test implémenté avec succès !**