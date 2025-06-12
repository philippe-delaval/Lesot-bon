# Attachements de Travaux

![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)
![Vue.js](https://img.shields.io/badge/Vue.js-3-green.svg)
![TypeScript](https://img.shields.io/badge/TypeScript-5-blue.svg)
![Inertia.js](https://img.shields.io/badge/Inertia.js-2.0-purple.svg)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4-teal.svg)

> **Application moderne de gestion d'attachements de travaux** avec interface utilisateur intuitive, conçue pour simplifier la création, la gestion et le suivi des attachements de travaux.

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

**Attachements de Travaux** est une application web moderne développée avec Laravel et Vue.js, permettant de gérer efficacement les attachements de travaux. L'application offre une interface utilisateur moderne et responsive, adaptée aux besoins des entreprises de construction et de services.

### Caractéristiques principales :
- Interface utilisateur moderne avec Vue 3 et TypeScript
- Navigation fluide avec InertiaJS (SPA sans API)
- Design responsive avec TailwindCSS
- Gestion complète des clients et attachements
- Signatures numériques intégrées
- Génération automatique de PDF
- Support multi-environnement (SQLite/PostgreSQL)

## ✨ Fonctionnalités

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
- **Design moderne** : Interface claire et professionnelle
- **Navigation intuitive** : Sidebar avec liens contextuels
- **Responsive design** : Adapté mobile, tablette et desktop
- **Notifications** : Système de toasts pour le feedback utilisateur
- **Breadcrumbs** : Navigation hiérarchique claire

### 🔧 Fonctionnalités Techniques
- **Multi-environnement** : SQLite (local) et PostgreSQL (production)
- **Migrations automatiques** : Scripts de backup et migration
- **Optimisations** : Cache, compilation assets, SEO
- **Sécurité** : Validation, authentification, autorisation

## 🛠 Technologies

### Backend
- **[Laravel 10](https://laravel.com/)** - Framework PHP moderne
- **[InertiaJS](https://inertiajs.com/)** - SPA sans API
- **[SQLite](https://sqlite.org/)** - Base de données locale
- **[PostgreSQL](https://postgresql.org/)** - Base de données production

### Frontend
- **[Vue.js 3](https://vuejs.org/)** - Framework JavaScript progressif
- **[TypeScript](https://typescriptlang.org/)** - Typage statique
- **[Vite](https://vitejs.dev/)** - Build tool moderne
- **[TailwindCSS](https://tailwindcss.com/)** - Framework CSS utility-first
- **[Lucide Icons](https://lucide.dev/)** - Icônes modernes

### Outils de Développement
- **[Composer](https://getcomposer.org/)** - Gestionnaire de dépendances PHP
- **[NPM](https://npmjs.com/)** - Gestionnaire de paquets Node.js
- **[ESLint](https://eslint.org/)** - Linter JavaScript/TypeScript
- **[Prettier](https://prettier.io/)** - Formateur de code

## 📋 Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- **PHP** >= 8.1
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

# Optionnel : Lancer les seeders pour des données de test
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

Si vous avez exécuté les seeders :
- **Email :** admin@example.com
- **Mot de passe :** password

## 💡 Utilisation

### Créer un Nouvel Attachement

1. **Navigation** : Cliquez sur "Nouvel Attachement" dans la sidebar
2. **Sélection Client** : Recherchez ou créez un nouveau client
3. **Détails Intervention** : Renseignez lieu, date, type d'ouvrage
4. **Fournitures** : Ajoutez les travaux exécutés
5. **Signatures** : Capturez les signatures numériques
6. **Sauvegarde** : L'attachement est automatiquement envoyé par email

### Gestion des Clients

```bash
# Accéder à la liste des clients
http://localhost:8000/clients

# Créer un nouveau client
http://localhost:8000/clients/create

# Voir les détails d'un client
http://localhost:8000/clients/{id}
```

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
├── Http/Controllers/     # Contrôleurs Laravel
└── Models/              # Modèles Eloquent

resources/js/
├── components/          # Composants Vue réutilisables
├── pages/              # Pages Inertia
└── layouts/            # Layouts de l'application
```

### API Documentation
```bash
# Générer la documentation API
php artisan route:list
```

## 🧪 Tests

### Tests Backend
```bash
# Tests Laravel
php artisan test

# Tests avec couverture
php artisan test --coverage
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
- 📧 Email : [philippe@example.com](mailto:contact@philippedelaval.com)
- 🐙 GitHub : [@philippe-delaval](https://github.com/philippe-delaval)
- 💼 LinkedIn : [Philippe Delaval](https://linkedin.com/in/philippe-delaval)

### Support
- **Issues GitHub** : [Créer une issue](https://github.com/philippe-delaval/Lesot-bon/issues)
- **Discussions** : [GitHub Discussions](https://github.com/philippe-delaval/Lesot-bon/discussions)
- **Wiki** : [Documentation complète](https://github.com/philippe-delaval/Lesot-bon/wiki)

---

## 🙏 Remerciements

Merci aux technologies et communautés qui rendent ce projet possible :

- **[Laravel](https://laravel.com/)** pour le framework backend robuste
- **[Vue.js](https://vuejs.org/)** pour l'écosystème frontend moderne
- **[InertiaJS](https://inertiajs.com/)** pour l'approche innovante SPA
- **[TailwindCSS](https://tailwindcss.com/)** pour le framework CSS utilitaire
- **[Lucide](https://lucide.dev/)** pour les icônes élégantes

---

<div align="center">

**⭐ Si ce projet vous est utile, n'hésitez pas à lui donner une étoile sur GitHub ! ⭐**

[![Made with ❤️](https://img.shields.io/badge/Made%20with-❤️-red.svg)](#)
[![Built with Laravel](https://img.shields.io/badge/Built%20with-Laravel-red.svg)](https://laravel.com)
[![Powered by Vue.js](https://img.shields.io/badge/Powered%20by-Vue.js-green.svg)](https://vuejs.org)

</div>
