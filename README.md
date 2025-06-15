# Système de Gestion Lesot

![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)
![Vue.js](https://img.shields.io/badge/Vue.js-3-green.svg)
![TypeScript](https://img.shields.io/badge/TypeScript-5-blue.svg)
![Inertia.js](https://img.shields.io/badge/Inertia.js-2.0-purple.svg)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4-teal.svg)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-blue.svg)
![SQLite](https://img.shields.io/badge/SQLite-3-lightblue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2-blueviolet.svg)

> **Application complète de gestion pour entreprise électrique** avec système d'attachements de travaux, gestion du personnel électrique, équipes spécialisées et planning intelligent, conçue pour simplifier les opérations et le suivi des interventions électriques.

## 📋 Table des Matières

- [Description](#description)
- [Fonctionnalités](#fonctionnalités)
- [Technologies](#technologies)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Démarrage](#démarrage)
- [Utilisation](#utilisation)
- [Configuration Base de Données](#configuration-base-de-données)
- [Déploiement](#déploiement)
- [Contribution](#contribution)
- [Documentation](#documentation)
- [Licence](#licence)
- [Contact](#contact)

## 📖 Description

**Système de Gestion Lesot** est une application web moderne développée avec Laravel 12 et Vue.js 3, spécialement conçue pour l'entreprise d'électricité Lesot à Saint-Laurent-Blangy. L'application offre une solution complète de gestion incluant les attachements de travaux, la gestion du personnel électrique qualifié, les équipes spécialisées et un système de planning intelligent.

### Caractéristiques principales :
- Interface utilisateur moderne avec Vue 3 et TypeScript
- Navigation fluide avec InertiaJS (SPA sans API)
- Design responsive avec TailwindCSS et Shadcn/ui
- Gestion complète des employés avec habilitations électriques
- Système d'équipes spécialisées par domaine électrique
- Planning intelligent avec suivi des interventions
- Gestion des clients et attachements de travaux
- Signatures numériques intégrées
- Génération automatique de PDF
- Support multi-environnement (SQLite/PostgreSQL)

## ✨ Fonctionnalités

### 👥 Gestion des Employés
- **CRUD complet** avec hiérarchie électrique (Gestionnaire → Chargé de projet → Employés)
- **Habilitations électriques** : B0, B1V, B2V, BR, BC, H0, H1V, H2V, HR, HC
- **Types de contrats** : CDI, CDD, Intérim, Apprentissage, Stage
- **Suivi des compétences** : Certifications, formations, expérience
- **Gestion des disponibilités** : Disponible, congé, formation, arrêt maladie
- **Affectation véhicules** et astreintes

### 🏢 Équipes Spécialisées
- **Spécialisations électriques** : Installation générale, Maintenance, Dépannage urgence, Industriel, Tertiaire, Particulier, Éclairage public
- **Gestion d'effectifs** avec capacités maximales
- **Affectation dynamique** des employés avec rôles (Chef d'équipe, Membre, Apprenti)
- **Zones d'intervention** et horaires de travail
- **Véhicules attribués** et compétences requises

### 📅 Planning Intelligent
- **Types d'affectations** : Intervention, Maintenance, Formation, Congé, Astreinte, Administratif
- **Suivi temps réel** : Planifié, En cours, Terminé, Annulé, Reporté
- **Gestion des conflits** et détection automatique
- **Suivi des performances** : Durée estimée vs réelle, retards, notes client
- **Géolocalisation** et frais de déplacement
- **Rapports d'intervention** et validation hiérarchique

### 🏢 Gestion des Clients
- **CRUD complet** : Création, lecture, mise à jour, suppression
- **Recherche avancée** : Filtrage par nom, email, ville
- **Adresses structurées** : Champs séparés pour une meilleure organisation
- **Historique des attachements** : Suivi des interventions par client

### 📄 Attachements de Travaux
- **Formulaire intuitif** : Interface améliorée avec fil d'Ariane
- **Sélecteur de clients** : Recherche en temps réel avec auto-complétion
- **Fournitures détaillées** : Tableau dynamique pour les travaux exécutés
- **Signatures numériques** : Capture des signatures entreprise et client
- **Génération PDF** : Export automatique des attachements
- **Géolocalisation** : Enregistrement automatique de la position

### 🎨 Interface Utilisateur
- **Design moderne** : Interface claire et professionnelle avec Shadcn/ui
- **Navigation intuitive** : Sidebar avec liens contextuels et icônes émojis
- **Responsive design** : Adapté mobile, tablette et desktop
- **Filtres avancés** : Recherche multicritères et tri intelligent
- **Statistiques temps réel** : Tableaux de bord avec KPI
- **Notifications** : Système de toasts pour le feedback utilisateur

### 🔧 Fonctionnalités Techniques
- **Multi-environnement** : SQLite (local) et PostgreSQL (production)
- **Migrations automatiques** : Scripts de backup et migration
- **Seeders réalistes** : Données de test pour l'industrie électrique
- **Optimisations** : Cache, compilation assets, SEO
- **Sécurité** : Validation, authentification, autorisation

## 🛠 Technologies

### Backend
- **[Laravel 12](https://laravel.com/)** - Framework PHP moderne avec fonctionnalités avancées
- **[InertiaJS](https://inertiajs.com/)** - SPA sans API pour une navigation fluide
- **[Eloquent ORM](https://laravel.com/docs/eloquent)** - Modèles avec relations complexes
- **[SQLite](https://sqlite.org/)** - Base de données locale pour développement
- **[PostgreSQL](https://postgresql.org/)** - Base de données production robuste
- **[PHP 8.2+](https://php.net/)** - Langage backend avec fonctionnalités modernes

### Frontend
- **[Vue.js 3](https://vuejs.org/)** - Framework JavaScript progressif avec Composition API
- **[TypeScript](https://typescriptlang.org/)** - Typage statique pour une meilleure maintenance
- **[Vite](https://vitejs.dev/)** - Build tool moderne et rapide
- **[TailwindCSS](https://tailwindcss.com/)** - Framework CSS utility-first
- **[Shadcn/ui](https://ui.shadcn.com/)** - Composants UI modernes et accessibles
- **[Lucide Icons](https://lucide.dev/)** - Icônes modernes et cohérentes
- **[Headless UI](https://headlessui.com/)** - Composants accessibles sans style

### Outils de Développement
- **[Composer](https://getcomposer.org/)** - Gestionnaire de dépendances PHP
- **[NPM](https://npmjs.com/)** - Gestionnaire de paquets Node.js
- **[ESLint](https://eslint.org/)** - Linter JavaScript/TypeScript
- **[Prettier](https://prettier.io/)** - Formateur de code
- **[Laravel Tinker](https://github.com/laravel/tinker)** - REPL pour Laravel
- **[Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar)** - Outils de debug

## 📋 Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- **PHP** >= 8.2
- **Node.js** >= 18.0
- **Composer** >= 2.0
- **NPM** ou **Yarn**
- **SQLite** (développement)
- **PostgreSQL** (production, optionnel)

### Vérification des versions :
```bash
php --version
node --version
composer --version
npm --version
```

## 🚀 Installation

### 1. Cloner le Dépôt
```bash
git clone https://github.com/philippe-delaval/Lesot-bon.git
cd Lesot-bon
```

### 2. Installer les Dépendances PHP
```bash
composer install
```

### 3. Installer les Dépendances Node.js
```bash
npm install
```

### 4. Configuration de l'Environnement
```bash
# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### 5. Configuration de la Base de Données

#### SQLite (Recommandé pour le développement)
```bash
# Créer le fichier de base de données
touch database/database.sqlite
```

#### PostgreSQL (Production)
```bash
# Copier la configuration de production
cp .env.production.example .env.production
# Éditer .env.production avec vos paramètres PostgreSQL
```

### 6. Migrations et Seeders
```bash
# Exécuter les migrations
php artisan migrate

# Lancer les seeders pour des données de test réalistes
php artisan db:seed
```

### 7. Build des Assets
```bash
# Développement
npm run dev

# Production
npm run build
```

## 🎯 Démarrage

### Serveur de Développement

```bash
# Terminal 1 : Serveur Laravel
php artisan serve

# Terminal 2 : Watcher Vite (assets)
npm run dev
```

L'application sera accessible à : **http://localhost:8000**

### Comptes par Défaut

Après avoir exécuté les seeders, vous pouvez utiliser :
- **Gestionnaire :** j.dubois@lesot-elec.fr
- **Chargé de projet :** s.martin@lesot-elec.fr / p.leroy@lesot-elec.fr
- **Employés :** Divers comptes disponibles (voir EmployeSeeder)

## 💡 Utilisation

### Gestion des Employés

1. **Navigation** : Cliquez sur "Employés" dans la sidebar
2. **Filtrage avancé** : Recherche par nom, statut, habilitation, disponibilité
3. **Détails employé** : Visualisation des compétences et affectations
4. **Habilitations** : Suivi des certifications électriques

### Gestion des Équipes

1. **Navigation** : Cliquez sur "👥 Équipes" dans la sidebar
2. **Spécialisations** : Installation, Maintenance, Dépannage, etc.
3. **Affectations** : Gestion dynamique des membres d'équipe
4. **Capacités** : Suivi de l'effectif et du taux d'occupation

### Planning des Interventions

1. **Navigation** : Cliquez sur "📅 Planning" dans la sidebar
2. **Vue d'ensemble** : Visualisation des planifications par période
3. **Types d'affectation** : Intervention, Formation, Congé, Astreinte
4. **Suivi temps réel** : Statuts et progression des tâches

### Créer un Nouvel Attachement

1. **Navigation** : Cliquez sur "Nouvel Attachement" dans la sidebar
2. **Sélection Client** : Recherchez ou créez un nouveau client
3. **Détails Intervention** : Renseignez lieu, date, type d'ouvrage
4. **Fournitures** : Ajoutez les travaux exécutés
5. **Signatures** : Capturez les signatures numériques
6. **Sauvegarde** : L'attachement est automatiquement envoyé par email

### Commandes Utiles

```bash
# Backup de la base SQLite
php artisan db:backup-sqlite --compress

# Migration des données SQLite vers PostgreSQL
php artisan db:migrate-sqlite-to-pgsql --dry-run

# Nettoyage du cache
php artisan optimize:clear

# Vérification du code
npm run lint
npm run format:check

# Regénérer les seeders avec nouvelles données
php artisan db:seed --class=EmployeSeeder
php artisan db:seed --class=EquipeSeeder
php artisan db:seed --class=PlanningSeeder
```

## 🗄 Configuration Base de Données

### Environnement Local (SQLite)
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

### Environnement de Production (PostgreSQL)
```env
APP_ENV=production
DB_CONNECTION=pgsql
DB_HOST=your-postgres-host
DB_PORT=5432
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password
DB_SSLMODE=require
```

### Migration Automatique
Le projet détecte automatiquement l'environnement :
- **Local** : Utilise SQLite par défaut
- **Production** (`APP_ENV=production`) : Utilise PostgreSQL automatiquement

## 🚢 Déploiement

### Déploiement Automatisé

```bash
# Script de déploiement complet
./scripts/deploy-postgresql.sh
```

### Déploiement Manuel

#### 1. Préparation de l'Environnement
```bash
# Configuration de production
cp .env.production.example .env
# Éditer .env avec vos paramètres

# Installation des dépendances (production uniquement)
composer install --no-dev --optimize-autoloader
npm ci --only=production
```

#### 2. Build et Optimisations
```bash
# Build des assets
npm run build

# Optimisations Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Nettoyage
php artisan optimize
```

#### 3. Migration de Base de Données
```bash
# Sauvegarde SQLite (si migration)
php artisan db:backup-sqlite --compress

# Migration vers PostgreSQL
php artisan migrate --force
php artisan db:migrate-sqlite-to-pgsql
```

#### 4. Permissions
```bash
# Permissions des dossiers
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Serveurs Supportés
- **Apache** avec mod_rewrite
- **Nginx** avec PHP-FPM
- **Laravel Forge**
- **Laravel Vapor**
- **Heroku** (avec buildpacks PHP et Node.js)

## 🤝 Contribution

Nous accueillons les contributions ! Voici comment participer :

### 1. Fork et Clone
```bash
# Fork le projet sur GitHub
git clone https://github.com/votre-username/Lesot-bon.git
cd Lesot-bon
```

### 2. Créer une Branche
```bash
git checkout -b feature/ma-nouvelle-fonctionnalite
```

### 3. Développement
```bash
# Installer les dépendances
composer install
npm install

# Tests
php artisan test
npm run test

# Linting
npm run lint
npm run format
```

### 4. Pull Request
```bash
git add .
git commit -m "feat: ajouter nouvelle fonctionnalité"
git push origin feature/ma-nouvelle-fonctionnalite
```

### Standards de Code
- **PSR-12** pour PHP
- **ESLint + Prettier** pour JavaScript/TypeScript
- **Tests unitaires** requis pour les nouvelles fonctionnalités
- **Documentation** des nouvelles APIs

### Issues et Bugs
- Utilisez les **templates d'issues** GitHub
- Fournissez les **étapes de reproduction**
- Incluez les **logs d'erreur** pertinents

## 📚 Documentation

### Documentation Technique
- **[DATABASE_MIGRATION_GUIDE.md](DATABASE_MIGRATION_GUIDE.md)** - Guide de migration des bases de données
- **[ATTACHEMENTS_README.md](ATTACHEMENTS_README.md)** - Documentation spécifique aux attachements

### Guides de Développement
```bash
# Structure du projet
app/
├── Console/Commands/     # Commandes Artisan personnalisées
├── Http/Controllers/     # Contrôleurs Laravel (Employe, Equipe, Planning)
└── Models/              # Modèles Eloquent avec relations complexes

resources/js/
├── components/          # Composants Vue réutilisables
├── pages/              # Pages Inertia (Employes, Equipes, Planning)
└── layouts/            # Layouts de l'application

database/
├── migrations/         # Migrations pour employés, équipes, planning
└── seeders/           # Seeders avec données réalistes électriques
```

### API Documentation
```bash
# Générer la documentation API
php artisan route:list

# Routes principales
# GET /employes - Liste des employés
# GET /equipes - Liste des équipes
# GET /planning - Planning des interventions
# GET /clients - Gestion des clients
# GET /attachements - Attachements de travaux
```

## 🧪 Tests

### Tests Backend
```bash
# Tests Laravel
php artisan test

# Tests avec couverture
php artisan test --coverage

# Test des modèles métier
php artisan test --filter=EmployeTest
php artisan test --filter=EquipeTest
php artisan test --filter=PlanningTest
```

### Tests Frontend
```bash
# Tests Vue/TypeScript
npm run test

# Tests E2E
npm run test:e2e
```

### Qualité de Code
```bash
# Analyse statique PHP
./vendor/bin/phpstan analyse

# Linting JavaScript/TypeScript
npm run lint
npm run lint:fix

# Formatting
npm run format
```

## 📄 Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

```
MIT License

Copyright (c) 2024 Lesot

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```

## 📞 Contact

### Mainteneur Principal
**Philippe Delaval**
- 📧 Email : [contact@philippedelaval.com](mailto:contact@philippedelaval.com)
- 🐙 GitHub : [@philippe-delaval](https://github.com/philippe-delaval)
- 💼 LinkedIn : [Philippe Delaval](https://linkedin.com/in/philippe-delaval)

### Support
- **Issues GitHub** : [Créer une issue](https://github.com/philippe-delaval/Lesot-bon/issues)
- **Discussions** : [GitHub Discussions](https://github.com/philippe-delaval/Lesot-bon/discussions)
- **Wiki** : [Documentation complète](https://github.com/philippe-delaval/Lesot-bon/wiki)

---

## 🙏 Remerciements

Un grand merci aux technologies et communautés exceptionnelles qui rendent ce projet possible :

### Frameworks et Outils Principaux
- **[Laravel](https://laravel.com/)** - Pour le framework backend robuste et élégant avec Eloquent ORM
- **[Vue.js](https://vuejs.org/)** - Pour l'écosystème frontend moderne et réactif
- **[InertiaJS](https://inertiajs.com/)** - Pour l'approche révolutionnaire SPA sans API
- **[TypeScript](https://typescriptlang.org/)** - Pour la sécurité et la maintenabilité du code

### Design et Interface
- **[TailwindCSS](https://tailwindcss.com/)** - Pour le framework CSS utilitaire performant
- **[Shadcn/ui](https://ui.shadcn.com/)** - Pour les composants UI modernes et accessibles
- **[Lucide](https://lucide.dev/)** - Pour les icônes élégantes et cohérentes
- **[Headless UI](https://headlessui.com/)** - Pour les composants accessibles sans style

### Outils de Développement
- **[Vite](https://vitejs.dev/)** - Pour l'outil de build ultra-rapide
- **[Composer](https://getcomposer.org/)** - Pour la gestion des dépendances PHP
- **[ESLint](https://eslint.org/)** & **[Prettier](https://prettier.io/)** - Pour la qualité et cohérence du code

### Bases de Données
- **[PostgreSQL](https://postgresql.org/)** - Pour la robustesse en production
- **[SQLite](https://sqlite.org/)** - Pour la simplicité en développement

### Communauté Open Source
Merci à tous les contributeurs open source qui partagent leur savoir et leurs outils, rendant possible la création d'applications modernes et performantes pour l'industrie électrique.

---

<div align="center">

**⭐ Si ce projet vous est utile, n'hésitez pas à lui donner une étoile sur GitHub ! ⭐**

[![Made with ❤️](https://img.shields.io/badge/Made%20with-❤️-red.svg)](#)
[![Built with Laravel 12](https://img.shields.io/badge/Built%20with-Laravel%2012-red.svg)](https://laravel.com)
[![Powered by Vue.js 3](https://img.shields.io/badge/Powered%20by-Vue.js%203-green.svg)](https://vuejs.org)
[![Designed with TailwindCSS](https://img.shields.io/badge/Designed%20with-TailwindCSS-teal.svg)](https://tailwindcss.com)

</div>