# Application d'Attachements de Travaux Numériques

Cette application remplace vos documents d'attachement de travaux papier par une solution numérique complète avec signatures électroniques.

## Fonctionnalités

- ✅ Formulaire numérique reprenant tous les champs de votre document papier
- ✅ Signatures électroniques tactiles (entreprise et client)
- ✅ Génération automatique de PDF
- ✅ Envoi automatique par email au client
- ✅ Géolocalisation de la signature
- ✅ Archivage et recherche des attachements
- ✅ Interface moderne et responsive

## Installation

### 1. Prérequis

- PHP 8.1+
- Node.js 18+
- MySQL/PostgreSQL
- Composer

### 2. Installation des dépendances

```bash
# Installer les dépendances PHP
composer install

# Installer les dépendances JavaScript
npm install
```

### 3. Configuration de la base de données

```bash
# Exécuter les migrations
php artisan migrate

# Créer le lien symbolique pour le stockage
php artisan storage:link
```

### 4. Configuration EmailJS

1. Créez un compte sur [EmailJS](https://www.emailjs.com/)
2. Créez un service email (Gmail, Outlook, etc.)
3. Créez un template d'email avec les variables suivantes :
   - `{{to_name}}` - Nom du client
   - `{{to_email}}` - Email du client
   - `{{from_name}}` - Nom de votre entreprise
   - `{{numero_dossier}}` - Numéro du dossier
   - `{{date_intervention}}` - Date de l'intervention
   - `{{lieu_intervention}}` - Lieu de l'intervention
   - `{{attachment}}` - Le PDF sera attaché ici

4. Ajoutez vos identifiants dans le fichier `.env` :

```env
VITE_EMAILJS_PUBLIC_KEY=votre_cle_publique
VITE_EMAILJS_SERVICE_ID=votre_service_id
VITE_EMAILJS_TEMPLATE_ID=votre_template_id
```

### 5. Configuration de l'application

Dans le fichier `.env`, configurez également :

```env
APP_NAME="Lesot - Attachements"
APP_URL=http://votre-domaine.com

# Email de l'entreprise (pour les notifications internes)
MAIL_FROM_ADDRESS=contact@lesot.fr
MAIL_FROM_NAME="Lesot"
```

## Utilisation

### Créer un nouvel attachement

1. Connectez-vous à l'application
2. Cliquez sur "Nouvel Attachement"
3. Remplissez le formulaire :
   - Informations client
   - Détails de l'intervention
   - Fournitures et travaux (ajoutez autant de lignes que nécessaire)
   - Temps total passé
4. Faites signer l'entreprise et le client sur l'écran tactile
5. Cliquez sur "Valider et Envoyer"

### Fonctionnement

1. **Validation** : Les données sont validées côté serveur
2. **Génération PDF** : Un PDF est généré avec toutes les informations et signatures
3. **Stockage** :
   - Les signatures sont sauvegardées comme images
   - Le PDF est stocké sur le serveur
   - Les données sont enregistrées en base de données
4. **Envoi email** : Le PDF est automatiquement envoyé au client
5. **Archivage** : Tout est archivé pour consultation ultérieure

### Consulter les attachements

- Accédez à la liste des attachements depuis le menu
- Utilisez les filtres pour rechercher :
  - Par numéro de dossier
  - Par nom de client
  - Par lieu d'intervention
  - Par période
- Cliquez sur l'œil pour voir les détails
- Cliquez sur la flèche pour télécharger le PDF

## Structure des données

### Table `attachements`

- `numero_dossier` : Numéro unique (généré automatiquement si vide)
- `client_nom` : Nom du client
- `client_email` : Email du client
- `client_adresse_facturation` : Adresse de facturation
- `lieu_intervention` : Lieu de l'intervention
- `date_intervention` : Date de l'intervention
- `designation_travaux` : Description des travaux
- `fournitures_travaux` : JSON contenant les lignes de fournitures
- `temps_total_passe` : Temps total en heures
- `signature_entreprise_path` : Chemin vers l'image de signature
- `signature_client_path` : Chemin vers l'image de signature
- `pdf_path` : Chemin vers le PDF généré
- `geolocation` : Coordonnées GPS de la signature
- `created_by` : ID de l'utilisateur créateur

## Personnalisation

### Modifier les informations de l'entreprise

Dans `resources/js/components/AttachementForm.vue`, modifiez :

```javascript
from_name: 'Lesot', // Ligne 249
```

### Ajouter des champs supplémentaires

1. Ajoutez le champ dans le formulaire Vue
2. Ajoutez la validation dans le contrôleur
3. Ajoutez la colonne dans la migration
4. Mettez à jour le modèle Eloquent

### Modifier le design du PDF

Le PDF est généré dans la fonction `generatePDF()` du composant Vue.
Vous pouvez modifier :

- La mise en page
- Les polices et tailles
- Les couleurs
- L'en-tête et le pied de page

## Sécurité

- Authentification requise pour accéder à l'application
- Validation des données côté serveur
- Signatures horodatées et géolocalisées
- Stockage sécurisé des fichiers
- Archivage avec traçabilité

## Support

Pour toute question ou problème :

1. Vérifiez les logs Laravel : `storage/logs/laravel.log`
2. Vérifiez la console du navigateur pour les erreurs JavaScript
3. Assurez-vous que EmailJS est correctement configuré
4. Vérifiez les permissions des dossiers de stockage

## Développement

```bash
# Lancer le serveur de développement
npm run dev

# Compiler pour la production
npm run build

# Lancer les tests
php artisan test
```
