<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employe;
use Carbon\Carbon;

class EmployeSeeder extends Seeder
{
    public function run(): void
    {
        $employes = [
            // Gestionnaire
            [
                'nom' => 'Dubois',
                'prenom' => 'Jean',
                'email' => 'j.dubois@lesot-elec.fr',
                'telephone' => '03.21.45.67.89',
                'matricule' => 'GEST001',
                'statut' => 'permanent',
                'type_contrat' => 'cdi',
                'date_debut' => '2015-03-01',
                'salaire_base' => 4200.00,
                'role_hierarchique' => 'gestionnaire',
                'charge_projet_id' => null,
                'gestionnaire_id' => null,
                'habilitations_electriques' => ['B0', 'B1V', 'B2V', 'BR', 'BC'],
                'certifications' => ['SST', 'CACES'],
                'competences' => ['Management', 'Gestion de projet', 'Formation'],
                'disponibilite' => 'disponible',
                'vehicule_attribue' => 'Renault Kangoo - AB-123-CD',
                'astreinte' => false,
                'notes' => 'Gestionnaire principal avec 15 ans d\'expérience'
            ],

            // Chargés de projet
            [
                'nom' => 'Martin',
                'prenom' => 'Sophie',
                'email' => 's.martin@lesot-elec.fr',
                'telephone' => '03.21.45.67.90',
                'matricule' => 'CP001',
                'statut' => 'permanent',
                'type_contrat' => 'cdi',
                'date_debut' => '2018-09-15',
                'salaire_base' => 3500.00,
                'role_hierarchique' => 'charge_projet',
                'charge_projet_id' => null,
                'gestionnaire_id' => 1, // Jean Dubois
                'habilitations_electriques' => ['B0', 'B1V', 'B2V', 'BR'],
                'certifications' => ['SST', 'Formation échafaudage'],
                'competences' => ['Installation électrique', 'Gestion d\'équipe', 'Tertiaire'],
                'disponibilite' => 'disponible',
                'vehicule_attribue' => 'Peugeot Partner - EF-456-GH',
                'astreinte' => true,
                'notes' => 'Spécialisée dans les projets tertiaires'
            ],

            [
                'nom' => 'Leroy',
                'prenom' => 'Pierre',
                'email' => 'p.leroy@lesot-elec.fr',
                'telephone' => '03.21.45.67.91',
                'matricule' => 'CP002',
                'statut' => 'permanent',
                'type_contrat' => 'cdi',
                'date_debut' => '2019-01-10',
                'salaire_base' => 3400.00,
                'role_hierarchique' => 'charge_projet',
                'charge_projet_id' => null,
                'gestionnaire_id' => 1,
                'habilitations_electriques' => ['B0', 'B1V', 'B2V', 'BR', 'H0'],
                'certifications' => ['SST', 'ATEX'],
                'competences' => ['Installation industrielle', 'Automatisme', 'Maintenance'],
                'disponibilite' => 'disponible',
                'vehicule_attribue' => 'Ford Transit - IJ-789-KL',
                'astreinte' => true,
                'notes' => 'Expert en installations industrielles'
            ],

            // Employés équipe installation
            [
                'nom' => 'Durand',
                'prenom' => 'Thomas',
                'email' => 't.durand@lesot-elec.fr',
                'telephone' => '06.12.34.56.78',
                'matricule' => 'EMP001',
                'statut' => 'permanent',
                'type_contrat' => 'cdi',
                'date_debut' => '2020-06-01',
                'salaire_base' => 2800.00,
                'role_hierarchique' => 'employe',
                'charge_projet_id' => 2, // Sophie Martin
                'gestionnaire_id' => 1,
                'habilitations_electriques' => ['B0', 'B1V', 'BR'],
                'certifications' => ['SST'],
                'competences' => ['Installation électrique', 'Câblage', 'Dépannage'],
                'disponibilite' => 'disponible',
                'vehicule_attribue' => null,
                'astreinte' => false,
                'notes' => 'Électricien confirmé'
            ],

            [
                'nom' => 'Moreau',
                'prenom' => 'Lucas',
                'email' => 'l.moreau@lesot-elec.fr',
                'telephone' => '06.23.45.67.89',
                'matricule' => 'EMP002',
                'statut' => 'permanent',
                'type_contrat' => 'cdi',
                'date_debut' => '2021-03-15',
                'salaire_base' => 2600.00,
                'role_hierarchique' => 'employe',
                'charge_projet_id' => 2,
                'gestionnaire_id' => 1,
                'habilitations_electriques' => ['B0', 'B1V'],
                'certifications' => ['SST'],
                'competences' => ['Installation électrique', 'Éclairage'],
                'disponibilite' => 'disponible',
                'vehicule_attribue' => null,
                'astreinte' => false,
                'notes' => 'Junior en formation'
            ],

            // Employés équipe maintenance
            [
                'nom' => 'Rousseau',
                'prenom' => 'Marie',
                'email' => 'm.rousseau@lesot-elec.fr',
                'telephone' => '06.34.56.78.90',
                'matricule' => 'EMP003',
                'statut' => 'permanent',
                'type_contrat' => 'cdi',
                'date_debut' => '2019-08-20',
                'salaire_base' => 2900.00,
                'role_hierarchique' => 'employe',
                'charge_projet_id' => 3, // Pierre Leroy
                'gestionnaire_id' => 1,
                'habilitations_electriques' => ['B0', 'B1V', 'B2V', 'BR'],
                'certifications' => ['SST', 'Travail en hauteur'],
                'competences' => ['Maintenance préventive', 'Dépannage', 'Automatisme'],
                'disponibilite' => 'disponible',
                'vehicule_attribue' => null,
                'astreinte' => true,
                'notes' => 'Spécialisée maintenance industrielle'
            ],

            [
                'nom' => 'Bernard',
                'prenom' => 'Alexandre',
                'email' => 'a.bernard@lesot-elec.fr',
                'telephone' => '06.45.67.89.01',
                'matricule' => 'EMP004',
                'statut' => 'permanent',
                'type_contrat' => 'cdi',
                'date_debut' => '2020-11-02',
                'salaire_base' => 2700.00,
                'role_hierarchique' => 'employe',
                'charge_projet_id' => 3,
                'gestionnaire_id' => 1,
                'habilitations_electriques' => ['B0', 'B1V', 'BR'],
                'certifications' => ['SST'],
                'competences' => ['Maintenance préventive', 'Tableau électrique'],
                'disponibilite' => 'disponible',
                'vehicule_attribue' => null,
                'astreinte' => false,
                'notes' => 'Électricien maintenance'
            ],

            // Intérimaires
            [
                'nom' => 'Garcia',
                'prenom' => 'Carlos',
                'email' => 'c.garcia@interim.fr',
                'telephone' => '06.56.78.90.12',
                'matricule' => 'INT001',
                'statut' => 'interimaire',
                'type_contrat' => 'interim',
                'date_debut' => '2024-01-15',
                'date_fin' => '2024-07-15',
                'salaire_base' => 2400.00,
                'role_hierarchique' => 'employe',
                'charge_projet_id' => 2,
                'gestionnaire_id' => 1,
                'habilitations_electriques' => ['B0'],
                'certifications' => [],
                'competences' => ['Installation électrique'],
                'disponibilite' => 'disponible',
                'vehicule_attribue' => null,
                'astreinte' => false,
                'notes' => 'Intérimaire en renfort'
            ],

            [
                'nom' => 'Petit',
                'prenom' => 'Julie',
                'email' => 'j.petit@lesot-elec.fr',
                'telephone' => '06.67.89.01.23',
                'matricule' => 'APP001',
                'statut' => 'permanent',
                'type_contrat' => 'apprenti',
                'date_debut' => '2024-09-01',
                'salaire_base' => 800.00,
                'role_hierarchique' => 'employe',
                'charge_projet_id' => 2,
                'gestionnaire_id' => 1,
                'habilitations_electriques' => ['B0'],
                'certifications' => [],
                'competences' => ['Formation'],
                'disponibilite' => 'formation',
                'vehicule_attribue' => null,
                'astreinte' => false,
                'notes' => 'Apprentie 1ère année'
            ],

            [
                'nom' => 'Lambert',
                'prenom' => 'Romain',
                'email' => 'r.lambert@lesot-elec.fr',
                'telephone' => '06.78.90.12.34',
                'matricule' => 'EMP005',
                'statut' => 'permanent',
                'type_contrat' => 'cdi',
                'date_debut' => '2017-05-10',
                'salaire_base' => 3000.00,
                'role_hierarchique' => 'employe',
                'charge_projet_id' => 2,
                'gestionnaire_id' => 1,
                'habilitations_electriques' => ['B0', 'B1V', 'B2V', 'BR'],
                'certifications' => ['SST', 'Photovoltaïque'],
                'competences' => ['Installation électrique', 'Photovoltaïque', 'Borne de recharge'],
                'disponibilite' => 'conge',
                'vehicule_attribue' => null,
                'astreinte' => false,
                'notes' => 'Expert énergies renouvelables - en congé jusqu\'au 20/06'
            ]
        ];

        // Créer d'abord le gestionnaire
        $gestionnaire = Employe::create($employes[0]);
        
        // Puis les chargés de projet en mettant à jour gestionnaire_id
        $employes[1]['gestionnaire_id'] = $gestionnaire->id;
        $chargeProjet1 = Employe::create($employes[1]);
        
        $employes[2]['gestionnaire_id'] = $gestionnaire->id;
        $chargeProjet2 = Employe::create($employes[2]);
        
        // Puis les employés en mettant à jour les IDs appropriés
        for ($i = 3; $i < count($employes); $i++) {
            if ($employes[$i]['charge_projet_id'] == 2) {
                $employes[$i]['charge_projet_id'] = $chargeProjet1->id;
            } elseif ($employes[$i]['charge_projet_id'] == 3) {
                $employes[$i]['charge_projet_id'] = $chargeProjet2->id;
            }
            $employes[$i]['gestionnaire_id'] = $gestionnaire->id;
            Employe::create($employes[$i]);
        }
    }
}